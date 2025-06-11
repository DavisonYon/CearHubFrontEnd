<?php
require('methods.php');
$location = $_GET['location'] ?? 'tybee-island-burton-4h-center';
$communityData = getCommunityById(getCommunityIDBySlug($location));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content />
  <title>CEAR Hub | <?php echo $communityData['cname']; ?></title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Leaflet CSS & JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="js/ext/leaflet-iconex.min.js"></script>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap" rel="stylesheet" />

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans flex flex-col min-h-screen">

  <!-- Loader -->
  <div id="loader" class="fixed inset-0 bg-gray-900 text-gray-300 z-[9999] flex flex-col justify-center items-center">
    <svg viewBox="0 0 500 150" xmlns="http://www.w3.org/2000/svg" width="500" height="150">
      <defs>
        <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
          <feGaussianBlur stdDeviation="3.5" result="blur"/>
          <feMerge>
            <feMergeNode in="blur"/>
            <feMergeNode in="SourceGraphic"/>
          </feMerge>
        </filter>
        <linearGradient id="glowGradient" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" stop-color="#00f0ff">
            <animate attributeName="stop-color" values="#00f0ff;#00ff95;#00f0ff" dur="3s" repeatCount="indefinite"/>
          </stop>
          <stop offset="100%" stop-color="#00ff95">
            <animate attributeName="stop-color" values="#00ff95;#00f0ff;#00ff95" dur="3s" repeatCount="indefinite"/>
          </stop>
        </linearGradient>
      </defs>
      <text x="50" y="100" font-size="80" font-family="Verdana, sans-serif" font-weight="bold" fill="url(#glowGradient)" filter="url(#glow)">
        CEAR Hub
      </text>
    </svg>
    <p class="mt-5 text-xl">
      Loading assets for 
      <span id="asset"><?php echo htmlspecialchars($communityData['cname'] ?? 'Community'); ?></span>...
    </p>
  </div>

  <!-- Header -->
  <header class="py-6 w-[85%] mx-auto">
    <div class="flex flex-wrap justify-between items-center">
      <a href="">
        <img src="assets/CEAR-Emblem_white_web.png" alt="CEAR Logo" class="h-10">
      </a>
      <div class="relative inline-block text-left">
        <button id="dropdownToggle" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-gray-700 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none">
          Select Community
        </button>
        <?php RenderDropdownMenu(); ?>
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-grow w-[85%] mx-auto">
    <h1 class="text-5xl text-center uppercase font-bold font-[Oswald]" id="communityName">
      <?php echo $communityData['cname']; ?>
    </h1>

    <div class="mt-6 text-center bg-gray-100 py-4 rounded text-black" role="alert">
      <p id="communityCurrentTemps">Loading weather...</p>
    </div>

    <div class="bg-blue-600 mt-8 rounded overflow-hidden" style="height: 400px;">
      <div id="map" class="h-full w-full"></div>
    </div>

    <div class="mt-8">
      <?php renderCommunityEvents($communityData['cid']); ?>
    </div>

    <div class="mt-10">
      <h3 class="text-2xl font-semibold border-b pb-2 mb-4">About</h3>
      <!-- About content here -->
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-gray-800 text-gray-300 py-6 mt-10 text-center text-sm ">
    <p><a href="https://www.cearhub.org/" class="text-blue-400 hover:underline">CEAR Hub</a></p>
    <p><a href="admin" class="text-blue-400 hover:underline">[ADMIN]</a></p>
    <p>Version: <span id="version" class="italic">default</span></p>
    <p>© 2025 CEAR Hub</p>
  </footer>

</body>
</html>


	<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
	<script>
		const link = document.createElement('link');
		link.rel = 'stylesheet';
		link.type = 'text/css';
		link.href = 'css/main.css?rndstr=' + Math.random().toString(36).substring(2);
		document.head.appendChild(link);
	</script>
	<script>
		DrawMap(<?php echo $communityData['cordslat']; ?>, <?php echo $communityData['cordslong']; ?>);

		function DrawMap(centerLat, centerLon) {
			const mapContainer = document.getElementById('map');

			if (typeof mapInstance !== 'undefined' && mapInstance !== null) {
				mapInstance.remove();
				mapInstance = null;
			}

			mapInstance = L.map('map', {
				center: [centerLat, centerLon],
				zoom: 14,
				dragging: false,
				scrollWheelZoom: "center",
			});

			L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 13,
				attribution: '&copy; OpenStreetMap contributors'
			}).addTo(mapInstance);
		}
	</script>
	<script>
	// Show the loader on initial load
	document.getElementById('loader').style.display = 'flex';

	const weatherPromise = new Promise((resolve) => {
		window.APIWeather = function(streamid) {
			const apiUrl = "https://api.sealevelsensors.org/v1.1/Datastreams(" + streamid + ")/Observations?$orderby=phenomenonTime%20desc&$top=1";
			$.ajax({
				url: apiUrl,
				method: 'GET',
				dataType: 'json',
				success: function(response) {
					if (response && response.value && response.value.length > 0) {
						const observation = response.value[0];
						const tempCelsius = observation.result;
						const tempFahrenheit = (tempCelsius * 9 / 5) + 32;
						const observationDate = new Date(observation.phenomenonTime);
						$('#communityCurrentTemps').html(
							`Temperature: ${tempFahrenheit.toFixed(2)} °F | Last recorded at: ${observationDate.toLocaleString()}`
						);
					} else {
						$('#communityCurrentTemps').html('No observation data found.');
					}
					resolve();
				},
				error: function(jqXHR, textStatus, errorThrown) {
					$('#communityCurrentTemps').html(`Failed to fetch data. Status: ${textStatus} | Error: ${errorThrown}`);
					resolve();
				}
			});
		};
});


	const timeoutPromise = new Promise(resolve => setTimeout(resolve, 2000));

	Promise.race([weatherPromise, timeoutPromise]).then(() => {
		document.getElementById('loader').style.display = 'none';
	});

	// Trigger the weather API
	APIWeather(<?php echo $communityData['streamid']; ?>);
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('dropdownToggle');
    const menu = document.getElementById('dropdownMenu');

    toggleButton.addEventListener('click', function () {
      menu.classList.toggle('hidden');
    });

    // Optional: hide dropdown when clicking outside
    document.addEventListener('click', function (event) {
      if (!toggleButton.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
      }
    });
  });
</script>

</html>
