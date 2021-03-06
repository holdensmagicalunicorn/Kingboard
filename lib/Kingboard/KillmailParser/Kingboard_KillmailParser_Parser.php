<?php
/**
 * Parser for killmails
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
class Kingboard_KillmailParser_Parser
{
    /**
     * Victim data
     *
     * @var array
     */
    protected $victim = array(
        'characterName'   => '',
        'characterID'     => 0,
        'corporationName' => '',
        'corporationID'   => 0,
        'allianceName'    => '',
        'allianceID'      => 0,
        'shipType'        => '',
        'shipTypeID'      => 0,
        'factionName'     => '',
        'factionID'       => 0
    );

    /**
     * Location data
     *
     * @var array
     */
    protected $location = array(
        'solarSystemName' => '',
        'solarSystemID'   => 0,
        'security'        => 0,
        'moon'            => '',
        'moonID'          => 0
    );

    /**
     * Attackers
     *
     * @var array
     */
    protected $attackers = array();

    /**
     * ID Hash of a killmail
     *
     * @var Kingboard_KillmailHash_IdHash
     */
    protected $idHash = null;

    /**
     * Items fitted / transported
     *
     * @var array
     */
    protected $items = array();

    /**
     * Plain text mail
     *
     * @var string
     */
    protected $plainMail = '';

    /**
     * Unix Timestamp of the kill
     *
     * @var integer
     */
    protected $killTime = 0;

    /**
     * Errors that render the killmail useless
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Parse the different parts of a killmail into
     * a multidimensional array which can be further processed
     *
     * @param string $mail
     */
    public function parse($mail)
    {
        $this->plainMail = $mail;
        
        $victimActive    = true;
        $involved        = false;
        $destroyed       = false;
        $dropped         = false;
        $tokens          = Kingboard_KillmailParser_Factory::findTokensForMail($mail);
        $currentAttacker = -1;
        
        // Clean the mail and explode by line
        $lines = explode(chr(10), $mail);
        $ids = new Kingboard_KillmailParser_IdFinder();
        $lastMainItem = null;
        reset($lines);

        while (count($lines) > 0)
        {
            $plainLine = array_shift($lines);
            try
            {
                // If it has a name token and a slash, we presume the contents
                // after the slash is the corporation name
                // This is done for NPC names and their corps, and a slash
                // is no valid item name, yet is valid in a killmail
                $strTools = Kingboard_Helper_String::getInstance();
                if ($strTools->stripos($tokens->name(), $plainLine) !== false && $strTools->strpos('/', $plainLine) !== false)
                {
                    $parts = explode('/', $plainLine);
                    $plainLine = array_shift($parts);
                    $corp = $tokens->corp() . implode('/', $parts);

                    // If the final blow token is present, move it from the corp name to attacker name
                    if ($strTools->stripos($tokens->finalBlow(), $corp))
                    {
                        $plainLine .= ' ' . $tokens->finalBlow();
                        $corp = str_replace($tokens->finalBlow(), '', $corp);
                    }
                    array_unshift($lines, $corp);
                    reset($lines);
                    unset($parts, $corp);
                }
                
                $line = new Kingboard_KillmailParser_Line($plainLine, $tokens);

                switch ($line->getType())
                {
                    case Kingboard_KillmailParser_Line::TYPE_TIME:
                        $this->killTime = $line->getValue();
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_NAME:
                        if ($involved)
                        {
                            $currentAttacker++;
                            $this->attackers[$currentAttacker] = array(
                                'characterName'   => '',
                                'characterID'     => 0,
                                'typeID'          => 0,
                                'corporationName' => '',
                                'corporationID'   => 0,
                                'allianceName'    => '',
                                'allianceID'      => 0,
                                'shipType'        => '',
                                'shipTypeID'      => 0,
                                'factionName'     => '',
                                'factionID'       => 0,
                                'weaponType'      => '',
                                'weaponTypeID'    => 0,
                                'finalBlow'       => false,
                                'securityStatus'  => 0.0
                            );
                            $this->attackers[$currentAttacker]['finalBlow']     = $line->hasFinalBlow();
                            $this->attackers[$currentAttacker]['characterName'] = $line->getValue();
                            $this->attackers[$currentAttacker]['characterID']   = $ids->getCharacterId($line->getValue());
                            $this->attackers[$currentAttacker]['entityType']    = Kingboard_Helper_EntityType::getEntityTypeByEntityId(
                                $this->attackers[$currentAttacker]['characterID']
                            );
                            if ($this->attackers[$currentAttacker]['entityType'] == Kingboard_Helper_EntityType::ENTITY_NPC)
                            {
                                $this->attackers[$currentAttacker]['shipTypeID'] = $this->attackers[$currentAttacker]['characterID'];
                                $this->attackers[$currentAttacker]['shipType'] = $this->attackers[$currentAttacker]['characterName'];
                                $this->attackers[$currentAttacker]['characterID'] = 0;
                                $this->attackers[$currentAttacker]['characterName'] = '';
                            }
                        }
                        else
                        {
                            $this->victim['characterName'] = $line->getValue();
                            $this->victim['characterID'] = $ids->getCharacterId($line->getValue());
                        }
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_ALLIANCE:
                        if ($victimActive)
                        {
                            $record =& $this->victim;
                        }
                        elseif ($involved)
                        {
                            $record =& $this->attackers[$currentAttacker];
                        }
                        
                        if (!$line->isEmpty())
                        {
                            $record['allianceName'] = $line->getValue();
                            $record['allianceID'] = $ids->getAllianceId($line->getValue());
                        }

                        unset($record);
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_CORP:
                        if ($victimActive)
                        {
                            $record =& $this->victim;
                        }
                        elseif ($involved)
                        {
                            $record =& $this->attackers[$currentAttacker];
                        }

                        if (!$line->isEmpty())
                        {
                            $record['corporationID'] = $ids->getCorporationId($line->getValue());
                            $record['corporationName'] = $line->getValue();
                        }

                        unset($record);
                        break;

                    case Kingboard_KillmailParser_Line::TYPE_DAMAGE:
                        if ($victimActive)
                        {
                            $this->victim['damageTaken'] = $line->getValue();
                        }
                        elseif ($involved)
                        {
                            $this->attackers[$currentAttacker]['damageDone'] = $line->getValue();
                        }

                        break;

                    case Kingboard_KillmailParser_Line::TYPE_FACTION:
                        if ($victimActive)
                        {
                            $record =& $this->victim;
                        }
                        elseif ($involved)
                        {
                            $record =& $this->attackers[$currentAttacker];
                        }

                        if (!$line->isEmpty())
                        {
                            $record['factionID'] = $ids->getFactionId($line->getValue());
                            $record['factionName'] = $line->getValue();
                        }

                        unset($record);
                        break;

                     case Kingboard_KillmailParser_Line::TYPE_ITEM:

                         $flag = 0;
                         if ($line->isDrone())
                         {
                             $flag = 87;
                         }
                         elseif ($line->isCargo())
                         {
                             $flag = 5;
                         }

                         $item = array(
                             'typeID' => $ids->getItemId($line->getValue()),
                             'typeName' => $line->getValue(),
                             'qtyDropped' => $dropped ? $line->getQty() : 0,
                             'qtyDestroyed' => !$dropped ? $line->getQty() : 0,
                             'flag' => $flag
                         );

                         if ($line->inContainer())
                         {
                             if (!isset($this->items[$lastMainItem]))
                             {
                                 throw new Kingboard_KillmailParser_KillmailErrorException('Container for item not found');
                             }

                             if (!isset($this->items[$lastMainItem]['items']))
                             {
                                 $this->items[$lastMainItem]['items'] = array();
                             }

                             $this->items[$lastMainItem]['items'][] = $item;
                         }
                         else
                         {
                             $lastMainItem = count($this->items);
                             $this->items[] = $item;
                         }
                         
                         unset($item, $flag);
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_SECURITY:
                         $value = (float) $line->getValue();
                         if ($victimActive)
                         {
                             $this->location['security'] = $value;
                         }
                         elseif ($involved)
                         {
                             $this->attackers[$currentAttacker]['securityStatus'] = $value;
                         }

                         unset($value);
                         break;

                    case Kingboard_KillmailParser_Line::TYPE_SHIP:
                        if ($victimActive)
                        {
                            $record =& $this->victim;
                        }
                        elseif ($involved)
                        {
                            $record =& $this->attackers[$currentAttacker];
                        }

                        if (!$line->isEmpty())
                        {
                            $record['shipType'] = $line->getValue();
                            $record['shipTypeID'] = $ids->getItemId($line->getValue());
                        }

                        unset($record);
                        break;

                     case Kingboard_KillmailParser_Line::TYPE_SWITCH_ATTACKERS:
                         $victimActive = false;
                         $involved = true;
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_SWITCH_DROPPED:
                         $dropped = true;
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_SYSTEM:
                         $this->location['solarSystemID']   = $ids->getSolarSystemId($line->getValue());
                         $this->location['solarSystemName'] = $line->getValue();
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_MOON:
                         if (!$line->isEmpty())
                         {
                            $system = Kingboard_EveSolarSystem::getInstanceByCriteria(array('Moons.itemName' => $line->getValue()));
                            if ($system)
                            {
                                $moons = $system->Moons;
                                foreach ($moons as $moon)
                                {
                                    if ($moon['itemName'] == $line->getValue())
                                    {
                                        $this->location['moon']   = $line->getValue();
                                        $this->location['moonID'] = $moon['itemID'];
                                        break;
                                    }
                                }
                            }
                         }
                         break;

                     case Kingboard_KillmailParser_Line::TYPE_WEAPON:
                         $this->attackers[$currentAttacker]['weaponTypeID'] = $ids->getItemId($line->getValue());
                         $this->attackers[$currentAttacker]['weaponType'] = $line->getValue();
                         break;
                }
            }
            catch (Kingboard_KillmailParser_KillmailErrorException $e)
            {
                $this->errors[] = $e->getMessage();
                continue;
            }

            // Save errors for log / output
            if ($line->hasError())
            {
                $this->errors[] = $line->getError();
            }
        }

        // Now we need to clean up the items array
        $this->items = $this->cleanItemsArray($this->items);

        return $this;
    }

    /**
     * Merges the items of an item array to a API layout
     * 
     * @param array $items
     * @return array
     */
    protected function cleanItemsArray($items)
    {
        $newItems = array();
        foreach ($items as $item)
        {
            $merged = false;
            if (!isset($item['items']))
            {
                foreach ($newItems as &$mergedItem)
                {
                    if ($item['typeName'] == $mergedItem['typeName'] && $item['flag'] == $mergedItem['flag'])
                    {
                        $mergedItem['qtyDropped'] += $item['qtyDropped'];
                        $mergedItem['qtyDestroyed'] += $item['qtyDestroyed'];
                        $merged = true;
                        break;
                    }
                }
            }

            if (!$merged)
            {
                $newItems[] = $item;
            }
        }
        return $newItems;
    }

    /**
     * Get the ID hash, generate one if not available
     *
     * @return string
     */
    protected function getIdHash()
    {
        if (!$this->idHash)
        {
            $this->idHash = Kingboard_KillmailHash_IdHash::getByData(array(
                'killTime'  => $this->getTime(),
                'victim'    => $this->getVictim(),
                'attackers' => $this->getAttackers(),
                'items'     => $this->getItems()
            ));
        }
        return $this->idHash->generateHash();
    }

    /**
     * Get all data as an array, in the same
     * layout it has in the database
     *
     * @return array
     */
    public function getDataArray()
    {
        $location = $this->getLocation();
        return array(
            'idHash'        => $this->getIdHash(),
            'killTime'      => $this->getTime(),
            'solarSystemID' => $location['solarSystemID'],
            'moonID'        => $location['moonID'],
            'location'      => array(
                'solarSystem' => $location['solarSystemName'],
                'security'    => $location['security'],
                'region'      => Kingboard_EveSolarSystem::getBySolarSystemId($location['solarSystemID'])->Region['itemName']
            ),
            'victim'        => $this->getVictim(),
            'attackers'     => $this->getAttackers(),
            'items'         => $this->getItems(),
            //'location'      => $this->getLocation(),
            'errors'        => $this->errors,
            'plainMail'     => $this->plainMail
        );
    }

    /**
     * Get the data model, create one if none set
     * It does not save a new one or any changes!
     *
     * @return Kingboard_Kill
     */
    public function getModel()
    {
        $model = Kingboard_Kill::getInstanceByIdHash($this->getIdHash());
        if (!$model)
        {
            $model = new Kingboard_Kill();
        }
        $model->injectDataFromMail($this->getDataArray());
        return $model;
    }

    /**
     * Get the vicitm array
     * Empty if no mail has been parsed
     *
     * @return array
     */
    public function getVictim()
    {
        return $this->victim;
    }

    /**
     * Get the system information
     *
     * @return array
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get the attackers
     *
     * @return array
     */
    public function getAttackers()
    {
        return $this->attackers;
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get the plain mail string
     *
     * @return string
     */
    public function getPlainMail()
    {
        return $this->plainMail;
    }

    /**
     * Get the kill time as timestamp
     *
     * @return MongoDate
     */
    public function getTime()
    {
        return new MongoDate((int) $this->killTime);
    }

    /**
     * Get an array of error messages
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
