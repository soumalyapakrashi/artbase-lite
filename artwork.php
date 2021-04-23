<?php

require "./CREDENTIALS.php";

$dsn = "mysql:host=" . $host . ";dbname=" . $dbname;
$pdo = new PDO($dsn, $user, $password);

session_start();

// If the user is not logged in, redirect to the login page
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] === 0) {
    header("Location: ./index.php");
    exit();
}

// If the artwork_title SESSION variable is not set or is set incorrectly, go to the details page
if(!isset($_SESSION["artwork_title"]) || $_SESSION["artwork_title"] === "") {
    if($_SESSION["userid"][0] === "A")  header("Location: ./artistdetails.php");
    else if($_SESSION["userid"][0] === "C")  header("Location: ./customerdetails.php");
    exit();
}

// If customer has pressed the Buy Now button
if(isset($_POST["buy"])) {
    // Set the status of the current artwork to Sold
    $query = "UPDATE ARTWORK SET status = \"Sold\" WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $statement = $pdo->query($query);

    // Update the transaction in the CUSTOMER_TRANSACTION table
    // First generate a transaction ID
    $tid = uniqid("T");
    // Get the price of the artwork
    $query = "SELECT price FROM ARTWORK WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $statement = $pdo->query($query);
    $price = $statement->fetch(PDO::FETCH_ASSOC);
    // Insert the data into the table
    $query = "INSERT INTO CUSTOMER_TRANSACTION VALUES (\"" . $tid . "\", \"" . $_SESSION["userid"] . "\", \"" . $_SESSION["artwork_title"] . "\", " . $price["price"] . ")";
    $statement = $pdo->query($query);

    // We are assuming that if a customer buys an artwork from an artist, then obviously that customer should be liking that artist.
    // So we add this artist to the CUSTOMER_ARTIST table. A customer might also like an artist and not buy anything from that artist but
    // we do not consider that situation here for simplicity reason.
    $query = "SELECT aid FROM ARTWORK WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $statement = $pdo->query($query);
    $aid = $statement->fetch(PDO::FETCH_ASSOC);

    // First check whether the record is already present or not. If not, then only insert the new record
    $query = "SELECT * FROM CUSTOMER_ARTIST WHERE cid = \"" . $_SESSION["userid"] . "\" AND aid = \"" . $aid["aid"] . "\"";
    $statement = $pdo->query($query);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if(!$result) {
        $query = "INSERT INTO CUSTOMER_ARTIST VALUES (\"" . $_SESSION["userid"] . "\", \"" . $aid["aid"] . "\")";
        $statement = $pdo->query($query);
    }

    // We are also assuming that the customer likes the kind of artworks he/she is buying.
    // So we add the kind of this artwork to the CUSTOMER_KIND table.
    // Again, this might not be the case, as the customer can like other kinds of artworks also which he/she has not bought but
    // for simplicity, we do not consider that here.

    // Get the kinds of the current artwork
    $query = "SELECT kind FROM ARTWORK_KIND WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $stmt_kind = $pdo->query($query);

    // Add the kinds to the CUSTOMER_KIND table if it is not already present
    while($kind = $stmt_kind->fetch(PDO::FETCH_ASSOC)) {
        $query = "SELECT * FROM CUSTOMER_KIND WHERE cid = \"" . $_SESSION["userid"] . "\" AND kind = \"" . $kind["kind"] . "\"";
        $statement = $pdo->query($query);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if(!$result) {
            $query = "INSERT INTO CUSTOMER_KIND VALUES (\"" . $_SESSION["userid"] . "\", \"" . $kind["kind"] . "\")";
            $statement = $pdo->query($query);
        }
    }

    // Move to the customerdetails page
    header("Location: ./customerdetails.php");
    exit();
}

// If the currently logged in user is an aritst
if($_SESSION["userid"][0] == "A") {
    $uid = $_SESSION["userid"];

    // Get the details of the artist from the database
    $query = "SELECT name, picture FROM ARTIST WHERE aid = \"" . $uid . "\"";
    $stmt_userdetails = $pdo->query($query);
    $user_details = $stmt_userdetails->fetch(PDO::FETCH_ASSOC);

    $name = $user_details["name"];
    $picture = $user_details["picture"];

    // Get the details of the artwork
    $query = "SELECT aid, type, year, size, price, picture, status FROM ARTWORK WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $stmt_artwork = $pdo->query($query);
    $artwork_details = $stmt_artwork->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT name FROM ARTIST WHERE aid = \"" . $artwork_details["aid"] . "\"";
    $stmt_artistname = $pdo->query($query);
    $artistname = $stmt_artistname->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT kind FROM ARTWORK_KIND WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $stmt_kind = $pdo->query($query);
}

else if($_SESSION["userid"][0] === "C") {
    $uid = $_SESSION["userid"];

    $query = "SELECT name FROM CUSTOMER WHERE cid = \"" . $uid . "\"";
    $stmt_userdetails = $pdo->query($query);
    $user_details = $stmt_userdetails->fetch(PDO::FETCH_ASSOC);

    $name = $user_details["name"];

    // Get the details of the artwork
    $query = "SELECT aid, type, year, size, price, picture, status FROM ARTWORK WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $stmt_artwork = $pdo->query($query);
    $artwork_details = $stmt_artwork->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT name FROM ARTIST WHERE aid = \"" . $artwork_details["aid"] . "\"";
    $stmt_artistname = $pdo->query($query);
    $artistname = $stmt_artistname->fetch(PDO::FETCH_ASSOC);

    $query = "SELECT kind FROM ARTWORK_KIND WHERE title = \"" . $_SESSION["artwork_title"] . "\"";
    $stmt_kind = $pdo->query($query);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./favicon.png" type="image/png">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

    <link rel="stylesheet" href="./style/general.css">
    <link rel="stylesheet" href="./style/navbar.css">

    <title>ArtBase | Artwork</title>
</head>
<body>
    <header>
        <nav>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 714.45 167.83" preseveAspectRatio="xMidYMid meet" id="brand-logo">
                <defs><clipPath id="clip-path" transform="translate(1.11 -17.2)"><polygon points="113 23.28 52 23.28 30 23.28 0 23.28 0 171.62 116 171.62 116 23.62 113 23.28" style="fill:none"/></clipPath></defs><title>Asset 1</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M135.06,65.55h14V81.08q6.25-9.18,13.19-13.72a26,26,0,0,1,14.45-4.55,24.94,24.94,0,0,1,12.11,3.62L181.64,78a19.13,19.13,0,0,0-7.23-1.85q-6.82,0-13.18,5.61t-9.67,17.43Q149,108.23,149,135.86V171.8h-14Z" transform="translate(1.11 -17.2)"/><path d="M210.3,26.1H224V65.55h21.68V77.37H224V171.8H210.3V77.37H191.65V65.55H210.3Z" transform="translate(1.11 -17.2)"/><path d="M265,171.8V24.53h13.67V83.81A57.72,57.72,0,0,1,297.93,68a52.2,52.2,0,0,1,23.32-5.23q22.53,0,38.49,16.36t15.95,39.7a54,54,0,0,1-16.1,39.35q-16.09,16.32-38.73,16.31A49.54,49.54,0,0,1,297.35,169a54.86,54.86,0,0,1-18.64-16.7V171.8Zm54.69-10.45A41.17,41.17,0,0,0,356.12,140a43.35,43.35,0,0,0-.05-43.06,41.9,41.9,0,0,0-15.44-16,40.07,40.07,0,0,0-20.7-5.71,42.53,42.53,0,0,0-21.29,5.71,39.77,39.77,0,0,0-15.49,15.38,43.82,43.82,0,0,0-5.41,21.78q0,18.45,12.13,30.86T319.73,161.35Z" transform="translate(1.11 -17.2)"/><path d="M504.45,65.55V171.8H491V153.54a58.8,58.8,0,0,1-19.28,15.72,52.11,52.11,0,0,1-23.39,5.27q-22.56,0-38.53-16.35t-16-39.8a53.87,53.87,0,0,1,16.12-39.26Q426,62.82,448.69,62.81a50.17,50.17,0,0,1,23.68,5.57A53.94,53.94,0,0,1,491,85.08V65.55ZM449.81,76a41.23,41.23,0,0,0-36.43,21.36,43,43,0,0,0,.05,42.92,41.89,41.89,0,0,0,15.46,15.95,40.43,40.43,0,0,0,20.82,5.71A42.73,42.73,0,0,0,471,156.28,39.61,39.61,0,0,0,486.44,141a43.65,43.65,0,0,0,5.41-21.75q0-18.43-12.14-30.82A40.23,40.23,0,0,0,449.81,76Z" transform="translate(1.11 -17.2)"/><path d="M586.63,77.17l-8.79,9.08q-11-10.64-21.44-10.64A16.21,16.21,0,0,0,545,80a13.56,13.56,0,0,0-4.75,10.26,15.2,15.2,0,0,0,3.92,9.86q3.92,4.78,16.44,11.23,15.27,7.91,20.74,15.23a27.76,27.76,0,0,1,5.39,16.7,30,30,0,0,1-9.2,22.17q-9.19,9.09-23,9.08a40.18,40.18,0,0,1-17.56-4,38.15,38.15,0,0,1-13.84-11l8.6-9.76q10.47,11.82,22.22,11.81a20,20,0,0,0,14-5.27,16.29,16.29,0,0,0,5.78-12.4,15.86,15.86,0,0,0-3.82-10.45q-3.81-4.48-17.22-11.33-14.38-7.41-19.58-14.65a27.6,27.6,0,0,1-5.18-16.5,26.85,26.85,0,0,1,8.26-20.12q8.26-8,20.89-8Q571.76,62.81,586.63,77.17Z" transform="translate(1.11 -17.2)"/><path d="M698.3,136.54l11.52,6.06a63,63,0,0,1-13.09,18A52.15,52.15,0,0,1,680,171a58.44,58.44,0,0,1-21,3.56q-26,0-40.67-17T603.67,119a56.85,56.85,0,0,1,12.41-36q15.75-20.12,42.13-20.12,27.18,0,43.4,20.61Q713.15,98,713.34,119.75H617.73q.39,18.51,11.83,30.35a37.76,37.76,0,0,0,28.26,11.84,45.23,45.23,0,0,0,15.79-2.83,41.26,41.26,0,0,0,13-7.49Q692,147,698.3,136.54Zm0-28.61q-2.73-10.94-8-17.48A37.79,37.79,0,0,0,676.5,79.9a42.54,42.54,0,0,0-18.09-4A38.93,38.93,0,0,0,631.52,86q-8.22,7.32-12.42,22Z" transform="translate(1.11 -17.2)"/><g style="clip-path:url(#clip-path)"><line x1="107.7" y1="5.55" x2="107.7" y2="164.41" style="fill:none"/><rect x="100.3" y="5.55" width="14.82" height="158.87"/><line x1="96.06" y1="3.41" x2="6.11" y2="164.41" style="fill:none;stroke:#000;stroke-miterlimit:10;stroke-width:14px"/></g></g></g>
            </svg>

            <div class="nav-links">
                <a href="./showcase.php">Showcase</a>
                <a href="./logout.php">Logout</a>
                <?php

                if($_SESSION["userid"][0] === "A") {
                    echo "<a href=\"./artistdetails.php\" class=\"account-info\">";
                    echo "<img src=" . $user_details["picture"] . " alt=\"Profile Picture\">";
                    echo "<p>" . $user_details["name"] . "</p>";
                    echo "</a>";
                }
                else if ($_SESSION["userid"][0] === "C") {
                    echo "<a href=\"./customerdetails.php\" class=\"account-info\">" . $user_details["name"] . "</a>";
                }

                ?>
            </div>
        </nav>
    </header>

    <div class="container artwork">
        <h1><?php echo $_SESSION["artwork_title"]; ?></h1>
        <img src=<?php echo "'" . $artwork_details["picture"] . "'"; ?> alt=<?php echo "'" . $_SESSION["artwork_title"] . "'"; ?>>
        <div class="artwork-details">
            <div>
                <h2>Artist Name</h2>
                <p><?php echo $artistname["name"]; ?></p>
                <h2>Type</h2>
                <p><?php echo $artwork_details["type"]; ?></p>
                <h2>Year</h2>
                <p><?php echo $artwork_details["year"]; ?></p>
                <h2>Size</h2>
                <p><?php echo $artwork_details["size"]; ?></p>
            </div>
            <div>
                <h2>Kind</h2>
                <?php
                echo "<p>";
                while($artwork_kind = $stmt_kind->fetch(PDO::FETCH_ASSOC)) {
                    echo $artwork_kind["kind"] . " ";
                }
                echo "</p>";
                ?>
                <h2>Price</h2>
                <p><?php echo (($artwork_details["price"]) ? "Rs. " . $artwork_details["price"] : "NA"); ?></p>
                <h2>Status</h2>
                <p><?php echo $artwork_details["status"]; ?></p>
            </div>
        </div>

        <?php

        if($_SESSION["userid"][0] === "C" && $artwork_details["status"] === "Sale") {
            echo "<form action=" . $_SERVER["PHP_SELF"] . " method=\"post\">";
            echo "<button class=\"btn btn-primary\" type=\"submit\" name=\"buy\">Buy Now</button>";
            echo "</form>";
        }

        ?>
    </div>
</body>
</html>