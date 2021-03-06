<?php

require_once dirname(__FILE__) . '/../../../../lib/Kingboard/KillmailParser/Kingboard_KillmailParser_Factory.php';

/**
 * Test class for Kingboard_KillmailParser_Factory.
 * Generated by PHPUnit on 2011-04-07 at 14:04:57.
 */
class Kingboard_KillmailParser_FactoryTest extends PHPUnit_Framework_TestCase {


    public function testFindTokensForEnglishMail() {
        $tokens = Kingboard_KillmailParser_Factory::findTokensForMail('Alliance:    Corp:');
        $this->assertInstanceOf('Kingboard_KillmailParser_EnglishTokens', $tokens);
    }
// @todo: Do we need ru tokens?
//    public function testFindTokensForRussionMail() {
//        // Remove the following lines when you implement this test.
//        $this->markTestIncomplete(
//                'This test has not been implemented yet.'
//        );
//    }

    public function testFindTokensForGermanMail() {
        $tokens = Kingboard_KillmailParser_Factory::findTokensForMail('Allianz:    Fraktion: Ziel: Zerstört: Zerstörte Gegenstände: Beteiligte Parteien:');
        $this->assertInstanceOf('Kingboard_KillmailParser_GermanTokens', $tokens);
    }

    public function testParseTextMail() {
        $mail = "2011.04.05 20:40

Victim: Falci
Corp: Project Stealth Squad
Alliance: Majesta Empire
Faction: NONE
Destroyed: Nidhoggur
System: 4NGK-F
Security: 0.0
Damage Taken: 504457

Involved parties:

Name: Colla Camins
Security: -5.2
Corp: Tormentum Insomniae
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: Peripheral Weapon Navigation Diameter
Damage Done: 66210

Name: ANGAL 2000 (laid the final blow)
Security: -5.0
Corp: FinFleet
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: Caldari Navy Bane Torpedo
Damage Done: 48805

Name: Magnifikus Erzverwirrer
Security: -2.6
Corp: FinFleet
Alliance: Raiden.
Faction: NONE
Ship: Purifier
Weapon: Caldari Navy Mjolnir Torpedo
Damage Done: 47561

Name: SmokeDog
Security: -4.1
Corp: Nex Exercitus
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: Caldari Navy Bane Torpedo
Damage Done: 47165

Name: Pheonix1985
Security: 4.5
Corp: FinFleet
Alliance: Raiden.
Faction: NONE
Ship: Nemesis
Weapon: Caldari Navy Inferno Torpedo
Damage Done: 43705

Name: time 3290
Security: 1.0
Corp: The Ankou
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: Caldari Navy Bane Torpedo
Damage Done: 40761

Name: Zhayan Joruni
Security: 5.0
Corp: The Ankou
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: Caldari Navy Bane Torpedo
Damage Done: 36200

Name: Hund
Security: -3.7
Corp: x13
Alliance: Raiden.
Faction: NONE
Ship: Manticore
Weapon: Caldari Navy Juggernaut Torpedo
Damage Done: 35981

Name: Kanuti
Security: 4.9
Corp: x13
Alliance: Raiden.
Faction: NONE
Ship: Purifier
Weapon: Caldari Navy Mjolnir Torpedo
Damage Done: 25984

Name: White Panter
Security: -4.8
Corp: Tormentum Insomniae
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: Bane Torpedo
Damage Done: 22427

Name: Abderraman III
Security: 1.5
Corp: Tormentum Insomniae
Alliance: Raiden.
Faction: NONE
Ship: Purifier
Weapon: Caldari Navy Mjolnir Torpedo
Damage Done: 21413

Name: DJ MULER
Security: 1.9
Corp: Tormentum Insomniae
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: 'Malkuth' Siege Missile Launcher I
Damage Done: 20963

Name: Namof Zomgbag
Security: 4.2
Corp: FinFleet
Alliance: Raiden.
Faction: NONE
Ship: Hound
Weapon: 'Arbalest' Siege Missile Launcher
Damage Done: 20611

Name: Mo Cuishle
Security: -1.1
Corp: Tormentum Insomniae
Alliance: Raiden.
Faction: NONE
Ship: Capsule
Weapon: 'Malkuth' Siege Missile Launcher I
Damage Done: 13264

Name: Galmar Masu
Security: -9.8
Corp: x13
Alliance: Raiden.
Faction: NONE
Ship: Arazu
Weapon: Warrior II
Damage Done: 7681

Name: Edsback
Security: 0.5
Corp: Capital Maintenance
Alliance: None
Faction: NONE
Ship: Manticore
Weapon: 'Malkuth' Siege Missile Launcher I
Damage Done: 3103

Name: Kajdil
Security: -1.3
Corp: Tormentum Insomniae
Alliance: Raiden.
Faction: NONE
Ship: Falcon
Weapon: Warrior II
Damage Done: 2623

Name: Comy 2
Security: -5.0
Corp: Nex Exercitus
Alliance: Raiden.
Faction: NONE
Ship: Rapier
Weapon: Warp Disruptor II
Damage Done: 0

Name: Alex Keys
Security: 4.5
Corp: FinFleet
Alliance: Raiden.
Faction: NONE
Ship: Falcon
Weapon: 'Umbra' I White Noise ECM
Damage Done: 0


Destroyed items:

Capital Remote Armor Repair System I
Capital Shield Transporter I
Heavy Energy Neutralizer II
Triage Module I
Capital Energy Transfer Array I
Cap Recharger II
ECCM - Ladar II
Damage Control II
Armor Explosive Hardener II
Capital Armor Repairer I
Large Capacitor Control Circuit I, Qty: 3

Dropped items:

Sensor Booster II
Scan Resolution
Armor Kinetic Hardener II
Armor Thermic Hardener II
Energized Adaptive Nano Membrane II
Exotic Dancers (Cargo)
Vespa EC-600 (Drone Bay)
Ogre II (Drone Bay)
Cap Recharger II, Qty: 2
";

        $this->assertInstanceOf('Kingboard_Kill', Kingboard_KillmailParser_Factory::parseTextMail($mail));
    }

}

?>
