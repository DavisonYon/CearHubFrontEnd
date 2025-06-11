<?php
require('config.php');


function getCommunityIDBySlug($slug) {
    // Connect to the database
    $conn = new mysqli(SQL_HOSTNAME, SQL_DB_AUTH_UN, SQL_DB_AUTH_PW, SQL_DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $slug = $conn->real_escape_string($slug);

    // Try to find the community ID by slug
    $sql = "SELECT cid FROM communities WHERE slug = '$slug' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $conn->close();
        return $row['cid'];
    }

    // If not found, return the ID of the first entry in the table
    $sql = "SELECT cid FROM communities ORDER BY cid ASC LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        $conn->close();
        return $row['cid'];
    }

    $conn->close();
    return null;
}


function getCommunityById($cid) {
    
    // Connect to the database
    $conn = new mysqli(SQL_HOSTNAME, SQL_DB_AUTH_UN, SQL_DB_AUTH_PW, SQL_DB_NAME);

    // Check for connection errors
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT * FROM communities WHERE cid = ?");
    $stmt->bind_param("i", $cid); // 'i' specifies the type is integer

    // Execute the statement
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Fetch data
    $data = $result->fetch_assoc();

    // Clean up
    $stmt->close();
    $conn->close();

    return $data ? $data : null;
}

function RenderDropdownMenu() {
    // Connect to the database
    $conn = new mysqli(SQL_HOSTNAME, SQL_DB_AUTH_UN, SQL_DB_AUTH_PW, SQL_DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT slug, cname FROM communities ORDER BY cname ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    // Output the dropdown menu
    echo '<ul id="dropdownMenu" class="hidden absolute mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" role="menu" aria-orientation="vertical">';

    while ($row = $result->fetch_assoc()) {
        $slug = htmlspecialchars($row['slug']);
        $cname = htmlspecialchars($row['cname']);
        echo "<li><a href=\"$slug\" class=\"block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900\" role=\"menuitem\">$cname</a></li>";
    }

    echo '</ul>';

    $stmt->close();
    $conn->close();
}

function renderCommunityEvents($cid) {
    // Connect to the database
    $conn = new mysqli(SQL_HOSTNAME, SQL_DB_AUTH_UN, SQL_DB_AUTH_PW, SQL_DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute SQL
    $stmt = $conn->prepare("SELECT eid, ename, edescription, eimg, edate FROM events WHERE cid = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // A simple message styled with Tailwind
        echo '<div class="text-center text-gray-400 p-8">No weather events exist for this community.</div>';
    } else {
        // Main grid container: 1 column on mobile, 2 on large screens, with a gap
        echo '<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">';

        while ($row = $result->fetch_assoc()) {
            // Sanitize data for security
            $eid = htmlspecialchars($row['eid']);
            $title = htmlspecialchars($row['ename'] ?: 'Weather Event');
            $desc = htmlspecialchars($row['edescription']);
            $date = htmlspecialchars(date("F d, Y", strtotime($row['edate'])));
            $img = htmlspecialchars($row['eimg']);

            // --- Tailwind CSS Card ---
            // On mobile (default): flex-col (image on top, text below)
            // On medium screens and up (md:): flex-row (image left, text right)
            echo <<<HTML
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col md:flex-row transform hover:scale-105 transition-transform duration-300">
                
                <div class="md:flex-shrink-0">
                    <img class="h-56 w-full object-cover md:w-56" src="{$img}" alt="Image for {$title}">
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <div>
                        <h3 class="text-xl font-bold text-white">{$title}</h3>
                        <p class="mt-2 text-base text-gray-400">{$desc}</p>
                    </div>
                    
                    <div class="mt-auto pt-4">
                        <p class="text-sm text-gray-500">{$date}</p>
                        <a href="event.html?eventid={$eid}" class="mt-3 group inline-flex items-center text-cyan-400 hover:text-cyan-300 font-semibold transition">
                            Explore
                            <span class="ml-2 transition-transform group-hover:translate-x-1" aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>
                
            </div>
            HTML;
        }

        echo '</div>'; // Close the grid container
    }

    $stmt->close();
    $conn->close();
}







?>