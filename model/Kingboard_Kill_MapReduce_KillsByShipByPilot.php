<?php
/**
 * creates / allows access to stats about which shiptype has been killed how often
 */
class Kingboard_Kill_MapReduce_KillsByShipByPilot extends King23_MongoObject implements ArrayAccess
{
    protected $_className = "Kingboard_Kill_MapReduce_KillsByShipByPilot";

    public static function mapReduceKills($pilotid)
    {
        return self::mapReduce(array('attackers.characterID' => (int) $pilotid));
    }

    public static function mapReduceLosses($pilotid)
    {
        return self::mapReduce(array('victim.characterID' => (int) $pilotid));
    }

    /**
     * run the map/reduce
     * @static
     * @return void
     */
    public static function mapReduce($filter)
    {
        $map = "function () {
            var ship = db.Kingboard_EveItem.findOne({typeID: parseInt(this.victim.shipTypeID)},{'marketGroup.parentGroup.marketGroupName':1});
            var info = {}
            info[this.victim.shipType] = 1;
            info[\"total\"] = 1;
            if(ship != null && ship.marketGroup != null)
                emit(ship.marketGroup.parentGroup.marketGroupName, info);
        }";
        $reduce = "function (k, vals) {
            var sums = {}
            var total = 0;
            vals.forEach(function(info) {
                info[\"total\"] = 0;
                for (var key in info)
                {
                    if(sums[key] === undefined)
                        sums[key] = 0;
                    sums[key] += info[key];
                    total += info[key];
                }
            });
            sums[\"total\"] = total;
            return sums;
        }";
        return King23_Mongo::mapReduce("Kingboard_Kill", array('inline' => 1), $map, $reduce, $filter);
    }

}
