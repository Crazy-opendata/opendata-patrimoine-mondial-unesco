<?php

namespace CrazyOpendata;

require_once __DIR__."/vendor/autoload.php";

use CrazyOpendata\Core\Config;
use CrazyOpendata\Core\Export;

$mysql_user  = $argv[1];
$mysql_pass  = $argv[2];
$mysql_db    = "data";
$mysql_host  = "localhost";
$mysql_table = "patrimoine";

$config = new Config(
    $mysql_user,
    $mysql_pass,
    $mysql_db,
    $mysql_host,
    $mysql_table
);

$dbh = $config->getDB();
$sth = $dbh->prepare("SELECT * FROM ".$config->mysqlTable." where multiple=0");
$sth->execute();
$liste = $sth->fetchAll(\PDO::FETCH_ASSOC);

foreach ($liste as $patrimoine) {
    $id_no = $patrimoine['id_no'];

    $sites = get_site_details_xml($id_no, 'fr');
    //$sitesEn = get_site_details($id_no, 'en');
    //$sites = array_replace_recursive($sitesFr, $sitesEn);

    if (sizeof($sites) < 2) {
        continue;
    }
    echo "WH site ".sprintf('%4s', $id_no)." -> ".sizeof($sites)." locations\n";

    // On tag le patrimone principal avec le id_multiple=000 pour signaler que c'est un lieu multiple
    $sth = $dbh->prepare("UPDATE ".$config->mysqlTable." SET id_multiple='000' where id_no=?");
    $sth->execute(array($id_no));

    // On supprime les locations multiples pour les réinsérer
    $sth = $dbh->prepare("DELETE FROM ".$config->mysqlTable." where id_no=? and multiple=1");
    $sth->execute(array($id_no));

    // On insère les lieux multiples
    $sth = $dbh->prepare("INSERT INTO ".$config->mysqlTable." (id, id_no, multiple, name_multiple, id_multiple, date_inscribed, area_hectares, latitude, longitude, coordonnees, name_fr, name_en, states_name_fr, states_name_en) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($sites as $site) {
        $sth->execute(array(
            $site['id'],
            $site['id_no'],
            $site['multiple'],
            $site['name_multiple'],
            $site['id_multiple'],
            $site['date_inscribed'],
            $site['area_hectares'],
            $site['latitude'],
            $site['longitude'],
            $site['coordonnees'],
            $patrimoine['name_fr'],
            $patrimoine['name_en'],
            $patrimoine['states_name_fr'],
            $patrimoine['states_name_en'],
        ));
    }
}

function get_site_details_xml($id_no, $lang)
{
    sleep(0.3);
    $html = file_get_contents("http://whc.unesco.org/$lang/list/$id_no/multiple=1");

    $sites = array();

    preg_match_all(
        '#<tr>\s*'
        ."<td[^>]*>\s*$id_no-(?<id_multiple>\d+)\s*</td>\s*" // numéro
        ."<td[^>]*>\s*(?<name_multiple>[^<]*)\s*(<br />)?\s*(?<lieu>[^>\n]*)\s*(?<departement>[^>]*)</td>\s*" // nom<br /> ville, depart, region, pays
        ."<td[^>]*>\s*(?<states_name_$lang>[^<]*)\s*</td>\s*" // pays
        ."<td[^>]*>\s*(?<latitude_decimal>[NS]\d+ \d+ \d+.\d+) <br>(?<longitude_decimal>[EW]\d+ \d+ \d+.\d+)(&nbsp;)?\s*</td>\s*" // coords
        ."<td[^>]*>\s*(?<area_hectares>.*?)\s*</td>\s*" // superficie
     #   ."<td[^>]*>\s*(?<date_inscribed>\d\d\d\d)\s*</td>\s*" // Date inscription
        ."(<td[^>]*>)?\s*.*?\s*</td>\s*" // Zone tampon ou </td> supplémentaire inutile
        .'</tr>'
        .'#ms',
        $html,
        $matches,
        PREG_SET_ORDER
    );

    foreach ($matches as $match) {
        $match['date_inscribed'] = '';
        $match['latitude'] = convertDMSToDecimal($match['latitude_decimal']);
        $match['longitude'] = convertDMSToDecimal($match['longitude_decimal']);
        $match['coordonnees'] = $match['latitude'].",".$match['longitude'];
        $match["name_multiple"] = trim($match["name_multiple"], " \n\t\r*");
        $lieu = trim($match['lieu'], ", \n\t\r");
        if ($lieu) {
            $match["name_multiple"] .= " ($lieu)";
        }
        $match['area_hectares'] = cleanup_area($match['area_hectares']);
        $match['multiple'] = 1;
        $match["states_name_$lang"] = trim($match["states_name_$lang"]);
        $match["id"] = $id_no.sprintf('%05d', $match['id_multiple']);
        $match["id_no"] = $id_no;

        $keys = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 'lieu', 'departement', 'latitude_decimal', 'longitude_decimal', "states_name_$lang");
        foreach ($keys as $j) {
            unset($match[$j]);
        }

        $sites[$match['id_multiple']] = $match;
    }
    return $sites;
}

function get_site_details_json($id_no, $lang)
{
    sleep(1);
    $json = file_get_contents("http://whc.unesco.org/?cid=31&l=$lang&id_site=$id_no&multiple=1&mode=json");
    $datas = json_decode($json, true);

    # TODO
    # Les données ne sont pas complètes ...
    $sites = array();
    return $sites;
}

function cleanup_area($text)
{
    $text = str_replace(",", ".", $text);
    $text = array_shift(explode('<br /', $text));
    $text = preg_replace("#.*?([\d\.]*).*?#ms", "$1", $text);
    return $text;
}
