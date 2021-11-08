<?php
function getNameAndCoord()
{
  $jsonIterator = file_get_contents('assets/location/infrastructures.geojson');
  $parsedJson = json_decode($jsonIterator);

  $objects = [];

  foreach ($parsedJson->features as $object) {
    array_push($objects, (object)[
      'name' => $object->properties->name,
      'address' => $object->properties->addr_full,
      'coord' => $object->geometry->coordinates,
      'distance' => countDistance(-6.204603054574429, 106.95149547229757, $object->geometry->coordinates[1], $object->geometry->coordinates[0], $object->properties->name)
    ]);
  }

  usort($objects, function ($a, $b) {
    return strcmp($a->distance, $b->distance);
  });
  return $objects;
}

function countDistance($myPosLat, $myPosLang, $desPosLat, $desPosLang, $name)
{
  if ($myPosLat < 0) $myPosLat = $myPosLat * -1;
  if ($myPosLang < 0) $myPosLang = $myPosLang * -1;
  if ($desPosLat < 0) $desPosLat = $desPosLat * -1;
  if ($desPosLang < 0) $desPosLang = $desPosLang * -1;

  $a = ($myPosLat - $desPosLat) * ($myPosLat - $desPosLat);
  $b =  ($myPosLang - $desPosLang)  * ($myPosLang - $desPosLang);
  $sum = $a + $b;
  $distance = sqrt($sum);

  return $distance;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Document</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/js/routing/dist/leaflet-routing-machine.css" />

  <style>
    #map {
      height: 500px;
    }

    .anyClass {
      height: 425px;
      overflow-y: scroll;
    }

    * {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body class="bg-dark">
  <div class="container-fluid mt-5 mb-5">
    <center>
      <b>
        <h1 style="color:white">Geography Information System By Muhammad Alfarizi Tazkia</h1>
      </b>
      <h3 style="color:white">( Distribution of schools in DKI Jakarta )</h3>
      <h6 style="color:white">Data Source From <b>HOT_InAWARESurvey_2017</b> at website <a href="https://openstreetmap.id/data-dki-jakarta/">https://openstreetmap.id/data-dki-jakarta/</a></h6>
    </center>
    <div class="row mt-5">
      <div class="col-md-4">
        <div class="card" style="width: 100%;">
          <div class="card-header">
            <h3 class="mt-2">
              <center>Feature List</center>
            </h3>
          </div>
          <ul class="list-group list-group-flush nav-pills nav-stacked anyClass">
            <?php
            foreach (getNameAndCoord() as $obj) {
            ?>
              <li class="list-group-item m-1">
                <h5><b>
                    <?php echo $obj->name; ?>
                  </b></h3>
                  <p><?= $obj->address ?></p>
                  <h6><?php echo "Latitude  : " . $obj->coord[1]; ?></h6>
                  <h6><?php echo "Longitude  : " . $obj->coord[0]; ?></h6>
                  <button class="btn btn-primary" onclick="return getRoute(<?= $obj->coord[1] ?>, <?= $obj->coord[0] ?>)">Show Directions</button> 
                  <a target="_blank" href="https://maps.google.com/?q=<?= $obj->coord[1] ?>,<?= $obj->coord[0] ?>">See on Google Maps</a>
              </li>

            <?php } ?>
          </ul>
        </div>
      </div>
      <div class="col">
        <div id="map"></div>
      </div>
    </div>
  </div>

  <!-- Make sure you put this AFTER Leaflet's CSS -->
  <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
  <script src="assets/js/leaflet.ajax.js"></script>
  <script src="assets/js/routing/dist/leaflet-routing-machine.js"></script>
  <script type="text/javascript">
    var geojsonMarkerOptions = {
      radius: 10,
      fillColor: "blue",
      color: "#ffffff",
      weight: 5,
      opacity: 1,
      fillOpacity: 0.5,
    };

    var mymap = L.map("map").setView(
      [-6.204603054574429, 106.95149547229757],
      12
    );
    L.tileLayer(
      "https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}", {
        attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
        maxZoom: 18,
        id: "mapbox/streets-v11",
        tileSize: 512,
        zoomOffset: -1,
        accessToken: "pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw",
      }
    ).addTo(mymap);

    var geojsonFeature = {
      type: "Feature",
      properties: {
        name: "Muhammad Alfarizi Tazkia",
        amenity: "My Position",
        popupContent: "Hey, I'm Here!",
      },
      geometry: {
        type: "Point",
        coordinates: [106.95149547229757, -6.204603054574429],
      },
    };

    // var myPosMarker = new L.Icon({
    //   i
    // })

    L.geoJSON(geojsonFeature, {
      pointToLayer: function(feature, latlng) {
        return L.circleMarker(latlng, geojsonMarkerOptions);
      },
    }).addTo(mymap).bindPopup('Muhammad Alfarizi Here!').openPopup();

    function getRoute(lat, lng) {
      console.log(lat, lng);
      var coord = L.latLng(lat, lng);
      var control = L.Routing.control({
      waypoints: [
        L.latLng(-6.204603054574429, 106.95149547229757),
        L.latLng(lat, lng)
      ],
      routeWhileDragging: true
    }).addTo(mymap);
    }
    
    function popUp(f, l) {
      var out = [];

      destLat = f.geometry['coordinates'][1];
      destLng = f.geometry['coordinates'][0];
      if (f.properties) {
        out.push('<h6><center><b>' + f.properties['name'] + '</b></center></h6>');
        out.push('School Level' + ": " + f.properties['school_typ']);
        out.push('Address' + ": " + f.properties['addr_full'] + ', ' + f.properties['addr_city']);
        out.push('Data Source' + ": " + f.properties['source']);
        out.push("<br><button class='btn btn-warning' onclick='getRoute("+destLat+","+destLng+")'> I want to go there </button>");
        l.bindPopup(out.join("<br />"));
      }
    }
    var jsonTest = new L.GeoJSON.AJAX(["assets/location/infrastructures.geojson"], {
      onEachFeature: popUp,
    }).addTo(mymap);

    
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>

</html>