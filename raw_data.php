<?php
include ('config/config.php');

$now = new DateTime();
$d = array();
$d["timestamp"] = $now->getTimestamp();
$swLat = isset($_POST['swLat']) ? $_POST['swLat'] : 0;
$neLng = isset($_POST['neLng']) ? $_POST['neLng'] : 0;
$swLng = isset($_POST['swLng']) ? $_POST['swLng'] : 0;
$neLat = isset($_POST['neLat']) ? $_POST['neLat'] : 0;
$oSwLat = isset($_POST['oSwLat']) ? $_POST['oSwLat'] : 0;
$oSwLng = isset($_POST['oSwLng']) ? $_POST['oSwLng'] : 0;
$oNeLat = isset($_POST['oNeLat']) ? $_POST['oNeLat'] : 0;
$oNeLng = isset($_POST['oNeLng']) ? $_POST['oNeLng'] : 0;
$luredonly = isset($_POST['luredonly']) ? $_POST['luredonly'] : false;
$lastpokemon = isset($_POST['lastpokemon']) ? $_POST['lastpokemon'] : false;
$lastgyms = isset($_POST['lastgyms']) ? $_POST['lastgyms'] : false;
$lastpokestops = isset($_POST['lastpokestops']) ? $_POST['lastpokestops'] : false;
$lastlocs = isset($_POST['lastslocs']) ? $_POST['lastslocs'] : false;
$lastspawns = isset($_POST['lastspawns']) ? $_POST['lastspawns'] : false;
$d["lastpokestops"] = isset($_POST['pokestops']) ? $_POST['pokestops'] : false;
$d["lastgyms"] = isset($_POST['gyms']) ? $_POST['gyms'] : false;
$d["lastslocs"] = isset($_POST['scanned']) ? $_POST['scanned'] : false;
$d["lastspawns"] = isset($_POST['spawnpoints']) ? $_POST['spawnpoints'] : false;
$d["lastpokemon"] = isset($_POST['pokemon']) ? $_POST['pokemon'] : false;
$timestamp = isset($_POST['timestamp']) ? $_POST['timestamp'] : 0;
$useragent = $_SERVER['HTTP_USER_AGENT'];

if (empty($swLat) || empty($swLng) || empty($neLat) || empty($neLng) || preg_match("/curl|libcurl/", $useragent)) {
  http_response_code(400);
  die();
}

if ($maxLatLng > 0 && ((($neLat - $swLat) > $maxLatLng) || (($neLng - $swLng) > $maxLatLng))) {
  http_response_code(400);
  die();
}

if (!validateToken($_POST['token'])) {
  http_response_code(400);
  die();
}

$newarea = false;

if (($oSwLng < $swLng) && ($oSwLat < $swLat) && ($oNeLat > $neLat) && ($oNeLng > $neLng)) {
  $newarea = false;
}
elseif (($oSwLat != $swLat) && ($oSwLng != $swLng) && ($oNeLat != $neLat) && ($oNeLng != $neLng)) {
  $newarea = true;
}
else {
  $newarea = false;
}

$d["oSwLat"] = $swLat;
$d["oSwLng"] = $swLng;
$d["oNeLat"] = $neLat;
$d["oNeLng"] = $neLng;
$ids = array();
$eids = array();
$reids = array();
global $noPokemon;

if (!$noPokemon) {
  if ($d["lastpokemon"] == "true") {
    if ($lastpokemon != 'true') {
      $d["pokemons"] = get_active($swLat, $swLng, $neLat, $neLng);
    }
    else {
      if ($newarea) {
        $d["pokemons"] = get_active($swLat, $swLng, $neLat, $neLng, 0, $oSwLat, $oSwLng, $oNeLat, $oNeLng);
      }
      else {
        $d["pokemons"] = get_active($swLat, $swLng, $neLat, $neLng, $timestamp);
      }
    }

    if (isset($_POST['eids'])) {
      $eids = explode(",", $_POST['eids']);
      foreach($d['pokemons'] as $elementKey => $element) {
        foreach($element as $valueKey => $value) {
          if ($valueKey == 'pokemon_id') {
            if (in_array($value, $eids)) {

              // delete this particular object from the $array

              unset($d['pokemons'][$elementKey]);
            }
          }
        }
      }
    }

    if (isset($_POST['reids'])) {
      $reids = explode(",", $_POST['reids']);
      $d["pokemons"] = $d["pokemons"] + (get_active_by_id($reids, $swLat, $swLng, $neLat, $neLng));
      $d["reids"] = !empty($_POST['reids']) ? $reids : null;
    }
  }
}

global $noPokestops;

if (!$noPokestops) {
  if ($d["lastpokestops"] == "true") {
    if ($lastpokestops != "true") {
      $d["pokestops"] = get_stops($swLat, $swLng, $neLat, $neLng, 0, 0, 0, 0, 0, $luredonly);
    }
    else {
      if ($newarea) {
        $d["pokestops"] = get_stops($swLat, $swLng, $neLat, $neLng, 0, $oSwLat, $oSwLng, $oNeLat, $oNeLng, $luredonly);
      }
      else {
        $d["pokestops"] = get_stops($swLat, $swLng, $neLat, $neLng, $timestamp, 0, 0, 0, 0, $luredonly);
      }
    }
  }
}

global $noGyms, $noRaids;

if (!$noGyms || !$noRaids) {
  if ($d["lastgyms"] == "true") {
    if ($lastgyms != "true") {
      $d["gyms"] = get_gyms($swLat, $swLng, $neLat, $neLng);
    }
    else {
      if ($newarea) {
        $d["gyms"] = get_gyms($swLat, $swLng, $neLat, $neLng, 0, $oSwLat, $oSwLng, $oNeLat, $oNeLng);
      }
      else {
        $d["gyms"] = get_gyms($swLat, $swLng, $neLat, $neLng, $timestamp);
      }
    }
  }
}

global $noSpawnPoints;

if (!$noSpawnPoints) {
  if ($d["lastspawns"] == "true") {
    if ($lastspawns != "true") {
      $d["spawnpoints"] = get_spawnpoints($swLat, $swLng, $neLat, $neLng);
    }
    else {
      if ($newarea) {
        $d["spawnpoints"] = get_spawnpoints($swLat, $swLng, $neLat, $neLng, 0, $oSwLat, $oSwLng, $oNeLat, $oNeLng);
      }
      else {
        $d["spawnpoints"] = get_spawnpoints($swLat, $swLng, $neLat, $neLng, $timestamp);
      }
    }
  }
}

global $noScannedLocations;

if (!$noScannedLocations) {
  if ($d["lastslocs"] == "true") {
    if ($lastlocs != "true") {
      $d["scanned"] = get_recent($swLat, $swLng, $neLat, $neLng);
    }
    else {
      if ($newarea) {
        $d["scanned"] = get_recent($swLat, $swLng, $neLat, $neLng, 0, $oSwLat, $oSwLng, $oNeLat, $oNeLng);
      }
      else {
        $d["scanned"] = get_recent($swLat, $swLng, $neLat, $neLng, $timestamp);
      }
    }
  }
}

$d['token'] = checkForTokenReset();
$jaysson = json_encode($d);
echo $jaysson;

function get_active($swLat, $swLng, $neLat, $neLng, $tstamp = 0, $oSwLat = 0, $oSwLng = 0, $oNeLat = 0, $oNeLng = 0)
{
  global $db;
  $datas = array();
  if ($swLat == 0) {
    $datas = $db->query("SELECT * FROM sightings WHERE expire_timestamp > :time", ['time' => time() ])->fetchAll();
  }
  elseif ($tstamp > 0) {
    $datas = $db->query("SELECT * 
FROM   sightings 
WHERE  expire_timestamp > :time 
AND    lat > :swLat 
AND    lon > :swLng 
AND    lat < :neLat 
AND    lon < :neLng", [':time' => time() , ':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }
  elseif ($oSwLat != 0) {
    $datas = $db->query("SELECT * 
FROM   sightings 
WHERE  expire_timestamp > :time 
       AND lat > :swLat
       AND lon > :swLng 
       AND lat < :neLat 
       AND lon < :neLng 
       AND NOT( lat > :oSwLat 
                AND lon > :oSwLng 
                AND lat < :oNeLat 
                AND lon < :oNeLng ) ", [':time' => time() , ':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng, ':oSwLat' => $oSwLat, ':oSwLng' => $oSwLng, ':oNeLat' => $oNeLat, ':oNeLng' => $oNeLng])->fetchAll();
  }
  else {
    $datas = $db->query("SELECT * 
FROM   sightings 
WHERE  expire_timestamp > :time 
AND    lat > :swLat 
AND    lon > :swLng 
AND    lat < :neLat 
AND    lon < :neLng", [':time' => time() , ':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }

  $pokemons = array();
  $json_poke = "static/data/pokemon.json";
  $json_contents = file_get_contents($json_poke);
  $data = json_decode($json_contents, TRUE);
  $i = 0;
  /* fetch associative array */
  foreach($datas as $row) {
    $p = array();
    $dissapear = $row["expire_timestamp"] * 1000;
    $lat = floatval($row["lat"]);
    $lon = floatval($row["lon"]);
    $pokeid = intval($row["pokemon_id"]);
    $atk = isset($row["atk_iv"]) ? intval($row["atk_iv"]) : null;
    $def = isset($row["def_iv"]) ? intval($row["def_iv"]) : null;
    $sta = isset($row["sta_iv"]) ? intval($row["sta_iv"]) : null;
    $mv1 = isset($row["move_1"]) ? intval($row["move_1"]) : null;
    $mv2 = isset($row["move_2"]) ? intval($row["move_2"]) : null;
    $weight = isset($row["weight"]) ? floatval($row["weight"]) : null;
    $height = isset($row["height"]) ? floatval($row["height"]) : null;
    $gender = isset($row["gender"]) ? intval($row["gender"]) : null;
    $form = isset($row["form"]) ? intval($row["form"]) : null;
    $cp = isset($row["cp"]) ? intval($row["cp"]) : null;
    $cpm = isset($row["cp_multiplier"]) ? floatval($row["cp_multiplier"]) : null;
    $level = isset($row["level"]) ? intval($row["level"]) : null;
    $p["disappear_time"] = $dissapear; //done
    $p["encounter_id"] = $row["encounter_id"]; //done
    global $noHighLevelData;
    if (!$noHighLevelData) {
      $p["individual_attack"] = $atk; //done
      $p["individual_defense"] = $def; //done
      $p["individual_stamina"] = $sta; //done
      $p["move_1"] = $mv1; //done
      $p["move_2"] = $mv2;
      $p["weight"] = $weight;
      $p["height"] = $height;
      $p["cp"] = $cp;
      $p["cp_multiplier"] = $cpm;
      $p["level"] = $level;
    }

    $p["latitude"] = $lat; //done
    $p["longitude"] = $lon; //done
    $p["gender"] = $gender;
    $p["form"] = $form;
    $p["pokemon_id"] = $pokeid;
    $p["pokemon_name"] = i8ln($data[$pokeid]['name']);
    $p["pokemon_rarity"] = i8ln($data[$pokeid]['rarity']);
    $types = $data[$pokeid]["types"];
    foreach($types as $k => $v) {
      $types[$k]['type'] = i8ln($v['type']);
    }

    $p["pokemon_types"] = $types;
    $p["spawnpoint_id"] = $row["spawn_id"];
    $pokemons[] = $p;
    unset($datas[$i]);
    $i++;
  }

  return $pokemons;
}

function get_active_by_id($ids, $swLat, $swLng, $neLat, $neLng)
{
  global $db;
  $datas = array();
  $pkmn_in = '';
  if (count($ids)) {
    $i = 1;
    foreach($ids as $id) {
      $pkmn_ids[':qry_' . $i] = $id;
      $pkmn_in.= ':' . 'qry_' . $i . ",";
      $i++;
    }

    $pkmn_in = substr($pkmn_in, 0, -1);
  }
  else {
    $pkmn_ids = [];
  }

  if ($swLat == 0) {
    $datas = $db->query("SELECT * 
FROM   sightings 
WHERE  `expire_timestamp` > :time
       AND pokemon_id IN ( $pkmn_in ) ", array_merge($pkmn_ids, [':time' => time() ]))->fetchAll();
  }
  else {
    $datas = $db->query("SELECT * 
FROM   sightings 
WHERE  expire_timestamp > :timeStamp
AND    pokemon_id IN ( $pkmn_in ) 
AND    lat > :swLat 
AND    lon > :swLng
AND    lat < :neLat
AND    lon < :neLng", array_merge($pkmn_ids, [':timeStamp' => time() , ':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng]))->fetchAll();
  }

  $pokemons = array();
  $json_poke = "static/data/pokemon.json";
  $json_contents = file_get_contents($json_poke);
  $data = json_decode($json_contents, TRUE);
  $i = 0;
  /* fetch associative array */
  foreach($datas as $row) {
    $p = array();
    $dissapear = $row["expire_timestamp"] * 1000;
    $lat = floatval($row["lat"]);
    $lon = floatval($row["lon"]);
    $pokeid = intval($row["pokemon_id"]);
    $atk = isset($row["atk_iv"]) ? intval($row["atk_iv"]) : null;
    $def = isset($row["def_iv"]) ? intval($row["def_iv"]) : null;
    $sta = isset($row["sta_iv"]) ? intval($row["sta_iv"]) : null;
    $mv1 = isset($row["move_1"]) ? intval($row["move_1"]) : null;
    $mv2 = isset($row["move_2"]) ? intval($row["move_2"]) : null;
    $weight = isset($row["weight"]) ? floatval($row["weight"]) : null;
    $height = isset($row["height"]) ? floatval($row["height"]) : null;
    $gender = isset($row["gender"]) ? intval($row["gender"]) : null;
    $form = isset($row["form"]) ? intval($row["form"]) : null;
    $cp = isset($row["cp"]) ? intval($row["cp"]) : null;
    $cpm = isset($row["cp_multiplier"]) ? floatval($row["cp_multiplier"]) : null;
    $level = isset($row["level"]) ? intval($row["level"]) : null;
    $p["disappear_time"] = $dissapear; //done
    $p["encounter_id"] = $row["encounter_id"]; //done
    global $noHighLevelData;
    if (!$noHighLevelData) {
      $p["individual_attack"] = $atk; //done
      $p["individual_defense"] = $def; //done
      $p["individual_stamina"] = $sta; //done
      $p["move_1"] = $mv1; //done
      $p["move_2"] = $mv2;
      $p["weight"] = $weight;
      $p["height"] = $height;
      $p["cp"] = $cp;
      $p["cp_multiplier"] = $cpm;
      $p["level"] = $level;
    }

    $p["latitude"] = $lat; //done
    $p["longitude"] = $lon; //done
    $p["gender"] = $gender;
    $p["form"] = $form;
    $p["pokemon_id"] = $pokeid;
    $p["pokemon_name"] = i8ln($data[$pokeid]['name']);
    $p["pokemon_rarity"] = i8ln($data[$pokeid]['rarity']);
    $p["pokemon_types"] = $data[$pokeid]["types"];
    $p["spawnpoint_id"] = $row["spawn_id"];
    $pokemons[] = $p;
    unset($datas[$i]);
    $i++;
  }

  return $pokemons;
}

function get_stops($swLat, $swLng, $neLat, $neLng, $tstamp = 0, $oSwLat = 0, $oSwLng = 0, $oNeLat = 0, $oNeLng = 0, $lured = false)
{
  global $db;
  $datas = array();
  if ($swLat == 0) {
    $datas = $db->query("SELECT external_id, lat, lon FROM pokestops")->fetchAll();
  }
  elseif ($tstamp > 0) {
    $datas = $db->query("SELECT external_id, 
       lat, 
       lon 
FROM   pokestops 
WHERE  lat > :swLat 
AND    lon > :swLng 
AND    lat < :neLat 
AND    lon < :neLng", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }
  elseif ($oSwLat != 0) {
    $datas = $db->query("SELECT external_id, 
       lat, 
       lon 
FROM   pokestops 
WHERE  lat > :swLat
       AND lon > :swLng 
       AND lat < :neLat 
       AND lon < :neLng
       AND NOT( lat > :oSwLat 
                AND lon > :oSwLng 
                AND lat < :oNeLat 
                AND lon < :oNeLng ) ", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng, ':oSwLat' => $oSwLat, ':oSwLng' => $oSwLng, ':oNeLat' => $oNeLat, ':oNeLng' => $oNeLng])->fetchAll();
  }
  else {
    $datas = $db->query("SELECT external_id, 
       lat, 
       lon 
FROM   pokestops 
WHERE  lat > :swLat 
AND    lon > :swLng 
AND    lat < :neLat 
AND    lon < :neLng", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }

  $i = 0;
  $pokestops = array();
  /* fetch associative array */
  foreach($datas as $row) {
    $p = array();
    $lat = floatval($row["lat"]);
    $lon = floatval($row["lon"]);
    $p["active_fort_modifier"] = isset($row["active_fort_modifier"]) ? $row["active_fort_modifier"] : null;
    $p["enabled"] = isset($row["enabled"]) ? boolval($row["enabled"]) : true;
    $p["last_modified"] = isset($row["last_modified"]) ? $row["last_modified"] * 1000 : 0;
    $p["latitude"] = $lat;
    $p["longitude"] = $lon;
    $p["lure_expiration"] = isset($row["lure_expiration"]) ? $row["lure_expiration"] * 1000 : null;
    $p["pokestop_id"] = $row["external_id"];
    $pokestops[] = $p;
    unset($datas[$i]);
    $i++;
  }

  return $pokestops;
}

function get_gyms($swLat, $swLng, $neLat, $neLng, $tstamp = 0, $oSwLat = 0, $oSwLng = 0, $oNeLat = 0, $oNeLng = 0)
{
  global $db;
  $datas = array();
  if ($swLat == 0) {
    $datas = $db->query("SELECT t3.external_id, 
       t3.lat, 
       t3.lon, 
       t1.last_modified, 
       t1.team, 
       t1.slots_available, 
       t1.guard_pokemon_id 
FROM   (SELECT fort_id, 
               Max(last_modified) AS MaxLastModified 
        FROM   fort_sightings 
        GROUP  BY fort_id) t2 
       LEFT JOIN fort_sightings t1 
              ON t2.fort_id = t1.fort_id 
                 AND t2.maxlastmodified = t1.last_modified 
       LEFT JOIN forts t3 
              ON t1.fort_id = t3.id")->fetchAll();
  }
  elseif ($tstamp > 0) {
    $datas = $db->query("SELECT t3.external_id, 
       t3.lat, 
       t3.lon, 
       t1.last_modified, 
       t1.team, 
       t1.slots_available, 
       t1.guard_pokemon_id 
FROM   (SELECT fort_id, 
               Max(last_modified) AS MaxLastModified 
        FROM   fort_sightings 
        GROUP  BY fort_id) t2 
       LEFT JOIN fort_sightings t1 
              ON t2.fort_id = t1.fort_id 
                 AND t2.maxlastmodified = t1.last_modified 
       LEFT JOIN forts t3 
              ON t1.fort_id = t3.id 
WHERE  t3.lat > :swLat 
       AND t3.lon > :swLng 
       AND t3.lat < :neLat 
       AND t3.lon < :neLng", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }
  elseif ($oSwLat != 0) {
    $datas = $db->query("SELECT t3.external_id, 
       t3.lat, 
       t3.lon, 
       t1.last_modified, 
       t1.team, 
       t1.slots_available, 
       t1.guard_pokemon_id 
FROM   (SELECT fort_id, 
               Max(last_modified) AS MaxLastModified 
        FROM   fort_sightings 
        GROUP  BY fort_id) t2 
       LEFT JOIN fort_sightings t1 
              ON t2.fort_id = t1.fort_id 
                 AND t2.maxlastmodified = t1.last_modified 
       LEFT JOIN forts t3 
              ON t1.fort_id = t3.id 
WHERE  t3.lat > :swLat 
       AND t3.lon > :swLng
       AND t3.lat < :neLat
       AND t3.lon < :neLng
       AND NOT( t3.lat > :oSwLat
                AND t3.lon > :oSwLng
                AND t3.lat < :oNeLat
                AND t3.lon < :oNeLng)", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng, ':oSwLat' => $oSwLat, ':oSwLng' => $oSwLng, ':oNeLat' => $oNeLat, ':oNeLng' => $oNeLng])->fetchAll();
  }
  else {
    $datas = $db->query("SELECT    t3.external_id, 
          t3.lat, 
          t3.lon, 
          t1.last_modified, 
          t1.team, 
          t1.slots_available, 
          t1.guard_pokemon_id 
FROM      ( 
                   SELECT   fort_id, 
                            Max(last_modified) AS maxlastmodified 
                   FROM     fort_sightings 
                   GROUP BY fort_id) t2 
LEFT JOIN fort_sightings t1 
ON        t2.fort_id = t1.fort_id 
AND       t2.maxlastmodified = t1.last_modified 
LEFT JOIN forts t3 
ON        t1.fort_id = t3.id 
WHERE     t3.lat > :swLat
AND       t3.lon > :swLng 
AND       t3.lat < :neLat 
AND       t3.lon < :neLng", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }

  $i = 0;
  $gyms = array();
  $gym_ids = array();
  $json_poke = "static/data/pokemon.json";
  $json_contents = file_get_contents($json_poke);
  $data = json_decode($json_contents, TRUE);
  /* fetch associative array */
  foreach($datas as $row) {
    $lat = floatval($row["lat"]);
    $lon = floatval($row["lon"]);
    $gpid = intval($row["guard_pokemon_id"]);
    $lm = $row["last_modified"] * 1000;
    $ls = isset($row["last_scanned"]) ? $row["last_scanned"] * 1000 : null;
    $ti = isset($row["team"]) ? intval($row["team"]) : null;
    $tc = isset($row["total_cp"]) ? intval($row["total_cp"]) : null;
    $sa = intval($row["slots_available"]);
    $p = array();
    $p["enabled"] = isset($row["enabled"]) ? boolval($row["enabled"]) : true;
    $p["guard_pokemon_id"] = $gpid;
    $p["gym_id"] = $row["external_id"];
    $p["slots_available"] = $sa;
    $p["last_modified"] = $lm;
    $p["last_scanned"] = $ls;
    $p["latitude"] = $lat;
    $p["longitude"] = $lon;
    $p["name"] = isset($row["name"]) ? $row["name"] : null;
    $p["team_id"] = $ti;
    $p["pokemon"] = [];
    $p['total_gym_cp'] = $tc;
    $gym_ids[] = $row["external_id"];
    $gyms[$row["external_id"]] = $p;
    unset($datas[$i]);
    $i++;
  }

  $j = 0;
  global $fork;
  $gyms_in = '';
  if (count($gym_ids)) {
    $i = 1;
    foreach($gym_ids as $id) {
      $gym_in_ids[':qry_' . $i] = $id;
      $gyms_in.= ':' . 'qry_' . $i . ",";
      $i++;
    }

    $gyms_in = substr($gyms_in, 0, -1);
  }
  else {
    $gym_in_ids = [];
  }

  if ($fork != "asner") $raids = $db->query("SELECT t1.fort_id, 
       level, 
       pokemon_id, 
       time_battle AS raid_start, 
       time_end    AS raid_end 
FROM   (SELECT fort_id, 
               Max(time_end) AS MaxTimeEnd 
        FROM   raids 
        GROUP  BY fort_id) t1 
       LEFT JOIN raids t2 
              ON t1.fort_id = t2.fort_id 
                 AND maxtimeend = time_end 
WHERE  t1.fort_id IN ( $gyms_in ) ", $gym_in_ids)->fetchAll();
  else $raids = $db->query("SELECT t3.external_id, 
       t1.fort_id, 
       raid_level AS level, 
       pokemon_id, 
       cp, 
       move_1, 
       move_2, 
       raid_start, 
       raid_end 
FROM   (SELECT fort_id, 
               Max(raid_end) AS MaxTimeEnd 
        FROM   raid_info 
        GROUP  BY fort_id) t1 
       LEFT JOIN raid_info t2 
              ON t1.fort_id = t2.fort_id 
                 AND maxtimeend = raid_end 
       JOIN forts t3 
         ON t2.fort_id = t3.id 
WHERE  t3.external_id IN ( $gyms_in ) ", $gym_in_ids)->fetchAll();
  foreach($raids as $raid) {
    if ($fork != "asner") $id = $raid["fort_id"];
    else $id = $raid["external_id"];
    $rpid = intval($raid['pokemon_id']);
    $gyms[$id]['raid_level'] = intval($raid['level']);
    if ($rpid) $gyms[$id]['raid_pokemon_id'] = $rpid;
    if ($rpid) $gyms[$id]['raid_pokemon_name'] = i8ln($data[$rpid]['name']);
    $gyms[$id]['raid_pokemon_cp'] = isset($raid['cp']) ? intval($raid['cp']) : null;
    $gyms[$id]['raid_pokemon_move_1'] = isset($raid['move_1']) ? intval($raid['move_1']) : null;
    $gyms[$id]['raid_pokemon_move_2'] = isset($raid['move_2']) ? intval($raid['move_2']) : null;
    $gyms[$id]['raid_start'] = $raid["raid_start"] * 1000;
    $gyms[$id]['raid_end'] = $raid["raid_end"] * 1000;
    unset($raids[$j]);
    $j++;
  }

  return $gyms;
}

function get_spawnpoints($swLat, $swLng, $neLat, $neLng, $tstamp = 0, $oSwLat = 0, $oSwLng = 0, $oNeLat = 0, $oNeLng = 0)
{
  global $db;
  $datas = array();
  if ($swLat == 0) {
    $datas = $db->query("SELECT lat, lon, spawn_id, despawn_time FROM spawnpoints WHERE updated > 0")->fetchAll();
  }
  elseif ($tstamp > 0) {
    $datas = $db->query("SELECT lat, 
       lon, 
       spawn_id, 
       despawn_time 
FROM   spawnpoints 
WHERE  updated > :updated
AND    lat > :swLat 
AND    lon > :swLng
AND    lat < :neLat 
AND    lon < :neLng", ['updated' => $tstamp, ':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }
  elseif ($oSwLat != 0) {
    $datas = $db->query("SELECT lat, 
       lon, 
       spawn_id, 
       despawn_time 
FROM   spawnpoints 
WHERE  updated > 0 
       AND lat > :swLat  
       AND lon > :swLng 
       AND lat < :neLat 
       AND lon <  :neLng  
       AND NOT( lat >  :oSwLat 
                AND lon >  :oSwLng
                AND lat <  :oNeLat
                AND lon <  :oNeLng ) ", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng, ':oSwLat' => $oSwLat, ':oSwLng' => $oSwLng, ':oNeLat' => $oNeLat, ':oNeLng' => $oNeLng])->fetchAll();
  }
  else {
    $datas = $db->query("SELECT lat, 
       lon, 
       spawn_id, 
       despawn_time 
FROM   spawnpoints 
WHERE  updated > 0 
AND    lat >  :swLat  
AND    lon >  :swLng 
AND    lat < :neLat 
AND    lon < :neLng", [':swLat' => $swLat, ':swLng' => $swLng, ':neLat' => $neLat, ':neLng' => $neLng])->fetchAll();
  }

  $spawnpoints = array();
  $i = 0;
  foreach($datas as $row) {
    $p = array();
    $p["latitude"] = floatval($row["lat"]);
    $p["longitude"] = floatval($row["lon"]);
    $p["spawnpoint_id"] = $row["spawn_id"];
    $p["time"] = intval($row["despawn_time"]);
    $spawnpoints[] = $p;
    unset($row[$i]);
    $i++;
  }

  return $spawnpoints;
}

function get_recent($swLat, $swLng, $neLat, $neLng, $tstamp = 0, $oSwLat = 0, $oSwLng = 0, $oNeLat = 0, $oNeLng = 0)
{
  global $db;
  $datas = array();
  $recent = array();
  $i = 0;
  foreach($datas as $row) {
    $p = array();
    $p["latitude"] = floatval($row["latitude"]);
    $p["longitude"] = floatval($row["longitude"]);
    $lm = $row["last_modified"] * 1000;
    $p["last_modified"] = $lm;
    $recent[] = $p;
    unset($datas[$i]);
    $i++;
  }

  return $recent;
}
