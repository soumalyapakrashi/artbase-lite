<?php

require "./CREDENTIALS.php";

// Values taken from CREDENTIALS.php
$dsn = "mysql:host=" . $host . ";dbname=" . $dbname;
$pdo = new PDO($dsn, $user, $password);

session_start();

if(isset($_POST["signup"])) {
    // Get the type of signup - artist or customer
    $signup_type = $_POST["typeRadios"];

    // For customer signup
    if($signup_type === "customer") {
        // Get the details of the customer
        $name = htmlentities($_POST["nameCustomer"]);
        $email = htmlentities($_POST["emailCustomer"]);
        $password = htmlentities($_POST["passwordCustomer"]);
        $address = htmlentities($_POST["addressCustomer"]);

        // For now, show alert
        // TODO: move validation to JavaScript so that form does not submit without properly filled out fields
        if($name == "" || $email == "" || $password == "")
            echo "<script>alert(\"Please fill out all the required fields\")</script>";
        else {
            // Check if account with the given email ID is already present or not
            $query = "SELECT email FROM ACCOUNT WHERE email=\"" . $email . "\"";
            $statement = $pdo->query($query);
            $account = $statement->fetch(PDO::FETCH_ASSOC);

            if(!empty($account)) {
                echo "<script>alert(\"Account already exists! Please login instead\")</script>";
                header("Location: ./index.php");
                exit();  // This is required to prevent any further code execution in this script as it is not required
            }

            // Generate a unique id
            $uid = uniqid("C");
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO ACCOUNT VALUES (\"" . $uid . "\", \"" . $email . "\", \"" . $hashed_password . "\")";
            $statement = $pdo->query($query);

            $query = "INSERT INTO CUSTOMER VALUES (\"" . $uid . "\", \"" . $name . "\", " . (($address === "") ? "NULL" : "\"" . $address . "\"") . ")";
            $statement = $pdo->query($query);

            $_SESSION["logged_in"] = 1;  // Set the user (customer) to be logged in
            $_SESSION["userid"] = $uid;

            header("Location: ./customerdetails.php");
            exit();
        }
    }

    // For artist signup
    else if($signup_type === "artist") {
        // Get the details of the artist
        $name = htmlentities($_POST["nameArtist"]);
        $email = htmlentities($_POST["emailArtist"]);
        $password = htmlentities($_POST["passwordArtist"]);
        $birth_place = htmlentities($_POST["birthplaceArtist"]);
        // $age = isset($_POST["ageArtist"]) ? htmlentities($_POST["ageArtist"]) : "";
        $age = $_POST["ageArtist"];
        $statementArtist = $_POST["statementArtist"];
        $picture = isset($_FILES["pictureArtist"]) ? $_FILES["pictureArtist"] : "";
        $facebook = $_POST["facebookArtist"];
        $instagram = $_POST["instagramArtist"];

        // TODO: Perform validation in JavaScript
        if($name == "" || $email == "" || $password == "" || $birth_place == "") {
            echo "<script>alert(\"Please fill out all the required fields\")</script>";
        }
        else {
            if($name == "" || $email == "" || $password == "" || $birth_place == "" || $statementArtist == "")
                echo "<script>alert(\"Please fill out all the required fields\")</script>";
            else {
                // Check if account with the given email ID is already present or not
                $query = "SELECT email FROM ACCOUNT WHERE email=\"" . $email . "\"";
                $statement = $pdo->query($query);
                $account = $statement->fetch(PDO::FETCH_ASSOC);

                if(!empty($account)) {
                    echo "<script>alert(\"Account already exists! Please login instead\")</script>";
                    header("Location: " . "./index.php");
                    exit();  // This is required to prevent any further code execution in this script as it is not required
                }

                // Generate a unique id
                $uid = uniqid("A");

                // Upload the picture if set
                $target_file = "";
                if($picture != "") {
                    // Value taken from CREDENTIALS.php
                    $target_dir = $artist_picture_dir;
                    $image_file_type = strtolower(pathinfo($_FILES["pictureArtist"]["name"], PATHINFO_EXTENSION));
                    $target_file = $target_dir . $uid . "." . $image_file_type;
                    $upload_ok = 1;

                    // Check whether image is an actual image or not
                    $check = getimagesize($_FILES["pictureArtist"]["tmp_name"]);
                    if($check === false) {
                        $upload_ok = 0;
                        echo "<script>alert(\"Invalid or corrupt image\")</script>";
                    }

                    // Allow certain file formats
                    if($image_file_type != "jpg" && $image_file_type != "jpeg" && $image_file_type != "png") {
                        $upload_ok = 0;
                        echo "<script>alert(\"Wrong image file type\")</script>";
                    }

                    // If everything is ok, attempt to upload the image
                    if($upload_ok === 1) {
                        move_uploaded_file($_FILES["pictureArtist"]["tmp_name"], $target_file);
                    }
                }

                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO ACCOUNT VALUES (\"" . $uid . "\", \"" . $email . "\", \"" . $hashed_password . "\")";
                $statement = $pdo->query($query);

                $query = "INSERT INTO ARTIST VALUES (\"" . $uid . "\", \"" . $name . "\", \"" . $birth_place . "\", " . ((is_null($age)) ? "NULL" : $age) . ", \"" . $statementArtist . "\", " . (($target_file === "") ? "NULL" : "\"" . $target_file . "\"") . ")";
                $statement = $pdo->query($query);

                // Insert the social links if given
                if(!is_null($facebook)) {
                    $query = "INSERT INTO ARTIST_SOCIAL VALUES (\"" . $uid . "\", \"" . $facebook . "\")";
                    $statement = $pdo->query($query);
                }

                if(!is_null($instagram)) {
                    $query = "INSERT INTO ARTIST_SOCIAL VALUES (\"" . $uid . "\", \"" . $instagram . "\")";
                    $statement = $pdo->query($query);
                }

                $_SESSION["logged_in"] = 1; // set the user (artist) to be logged in
                $_SESSION["userid"] = $uid;

                header("Location: ./artistdetails.php");
                exit();
            }
        }
    }
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
    <link rel="stylesheet" href="./style/signup.css">
    <link rel="stylesheet" href="./style/navbar.css">

    <title>ArtBase | Sign Up</title>
</head>
<body>
    <header>
        <nav>
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 714.45 167.83" preseveAspectRatio="xMidYMid meet" id="brand-logo">
                <defs><clipPath id="clip-path" transform="translate(1.11 -17.2)"><polygon points="113 23.28 52 23.28 30 23.28 0 23.28 0 171.62 116 171.62 116 23.62 113 23.28" style="fill:none"/></clipPath></defs><title>Asset 1</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M135.06,65.55h14V81.08q6.25-9.18,13.19-13.72a26,26,0,0,1,14.45-4.55,24.94,24.94,0,0,1,12.11,3.62L181.64,78a19.13,19.13,0,0,0-7.23-1.85q-6.82,0-13.18,5.61t-9.67,17.43Q149,108.23,149,135.86V171.8h-14Z" transform="translate(1.11 -17.2)"/><path d="M210.3,26.1H224V65.55h21.68V77.37H224V171.8H210.3V77.37H191.65V65.55H210.3Z" transform="translate(1.11 -17.2)"/><path d="M265,171.8V24.53h13.67V83.81A57.72,57.72,0,0,1,297.93,68a52.2,52.2,0,0,1,23.32-5.23q22.53,0,38.49,16.36t15.95,39.7a54,54,0,0,1-16.1,39.35q-16.09,16.32-38.73,16.31A49.54,49.54,0,0,1,297.35,169a54.86,54.86,0,0,1-18.64-16.7V171.8Zm54.69-10.45A41.17,41.17,0,0,0,356.12,140a43.35,43.35,0,0,0-.05-43.06,41.9,41.9,0,0,0-15.44-16,40.07,40.07,0,0,0-20.7-5.71,42.53,42.53,0,0,0-21.29,5.71,39.77,39.77,0,0,0-15.49,15.38,43.82,43.82,0,0,0-5.41,21.78q0,18.45,12.13,30.86T319.73,161.35Z" transform="translate(1.11 -17.2)"/><path d="M504.45,65.55V171.8H491V153.54a58.8,58.8,0,0,1-19.28,15.72,52.11,52.11,0,0,1-23.39,5.27q-22.56,0-38.53-16.35t-16-39.8a53.87,53.87,0,0,1,16.12-39.26Q426,62.82,448.69,62.81a50.17,50.17,0,0,1,23.68,5.57A53.94,53.94,0,0,1,491,85.08V65.55ZM449.81,76a41.23,41.23,0,0,0-36.43,21.36,43,43,0,0,0,.05,42.92,41.89,41.89,0,0,0,15.46,15.95,40.43,40.43,0,0,0,20.82,5.71A42.73,42.73,0,0,0,471,156.28,39.61,39.61,0,0,0,486.44,141a43.65,43.65,0,0,0,5.41-21.75q0-18.43-12.14-30.82A40.23,40.23,0,0,0,449.81,76Z" transform="translate(1.11 -17.2)"/><path d="M586.63,77.17l-8.79,9.08q-11-10.64-21.44-10.64A16.21,16.21,0,0,0,545,80a13.56,13.56,0,0,0-4.75,10.26,15.2,15.2,0,0,0,3.92,9.86q3.92,4.78,16.44,11.23,15.27,7.91,20.74,15.23a27.76,27.76,0,0,1,5.39,16.7,30,30,0,0,1-9.2,22.17q-9.19,9.09-23,9.08a40.18,40.18,0,0,1-17.56-4,38.15,38.15,0,0,1-13.84-11l8.6-9.76q10.47,11.82,22.22,11.81a20,20,0,0,0,14-5.27,16.29,16.29,0,0,0,5.78-12.4,15.86,15.86,0,0,0-3.82-10.45q-3.81-4.48-17.22-11.33-14.38-7.41-19.58-14.65a27.6,27.6,0,0,1-5.18-16.5,26.85,26.85,0,0,1,8.26-20.12q8.26-8,20.89-8Q571.76,62.81,586.63,77.17Z" transform="translate(1.11 -17.2)"/><path d="M698.3,136.54l11.52,6.06a63,63,0,0,1-13.09,18A52.15,52.15,0,0,1,680,171a58.44,58.44,0,0,1-21,3.56q-26,0-40.67-17T603.67,119a56.85,56.85,0,0,1,12.41-36q15.75-20.12,42.13-20.12,27.18,0,43.4,20.61Q713.15,98,713.34,119.75H617.73q.39,18.51,11.83,30.35a37.76,37.76,0,0,0,28.26,11.84,45.23,45.23,0,0,0,15.79-2.83,41.26,41.26,0,0,0,13-7.49Q692,147,698.3,136.54Zm0-28.61q-2.73-10.94-8-17.48A37.79,37.79,0,0,0,676.5,79.9a42.54,42.54,0,0,0-18.09-4A38.93,38.93,0,0,0,631.52,86q-8.22,7.32-12.42,22Z" transform="translate(1.11 -17.2)"/><g style="clip-path:url(#clip-path)"><line x1="107.7" y1="5.55" x2="107.7" y2="164.41" style="fill:none"/><rect x="100.3" y="5.55" width="14.82" height="158.87"/><line x1="96.06" y1="3.41" x2="6.11" y2="164.41" style="fill:none;stroke:#000;stroke-miterlimit:10;stroke-width:14px"/></g></g></g>
            </svg>
        </nav>
    </header>

    <div class="container signup">
        <h2>Sign Up for ArtBase</h2>

        <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" enctype="multipart/form-data">
            <!-- We have either an artist signup or a customer signup -->
            <div class="form-check">
                <input class="form-check-input" type="radio" name="typeRadios" id="customerRadio" value="customer" checked onchange="showForm(this)">
                <label class="form-check-label" for="customerRadio">Customer</label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="radio" name="typeRadios" id="artistRadio" value="artist" onchange="showForm(this)">
                <label class="form-check-label" for="artistRadio">Artist</label>
            </div>

            <!-- Signup form for customers. Fields with * are required -->
            <div class="customer">
                <div class="form-group">
                    <label for="nameCustomer">Name <span class="span-form-required">*</span></label>
                    <input type="text" name="nameCustomer" id="nameCustomer" class="form-control" autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="emailCustomer">Email <span class="span-form-required">*</span></label>
                    <input type="email" name="emailCustomer" id="emailCustomer" class="form-control" autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="passwordCustomer">Password <span class="span-form-required">*</span></label>
                    <input type="password" name="passwordCustomer" id="passwordCustomer" class="form-control" autocomplete="password">
                </div>
                <div class="form-group">
                    <label for="addressCustomer">Address</label>
                    <input type="text" name="addressCustomer" id="addressCustomer" class="form-control" autocomplete="street-address">
                </div>
            </div>

            <!-- Signup form for artists. Fields with * are required -->
            <div class="artist">
                <div class="form-group">
                    <label for="nameArtist">Name <span class="span-form-required">*</span></label>
                    <input type="text" name="nameArtist" id="nameArtist" class="form-control" autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="emailArtist">Email <span class="span-form-required">*</span></label>
                    <input type="email" name="emailArtist" id="emailArtist" class="form-control" autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="passwordArtist">Password <span class="span-form-required">*</span></label>
                    <input type="password" name="passwordArtist" id="passwordArtist" class="form-control" autocomplete="password">
                </div>
                <div class="form-group">
                    <label for="birthplaceArtist">Birth Place <span class="span-form-required">*</span></label>
                    <input type="text" name="birthplaceArtist" id="birthplaceArtist" class="form-control">
                </div>
                <div class="form-group">
                    <label for="ageArtist">Age</label>
                    <input type="number" name="ageArtist" id="ageArtist" class="form-control">
                </div>
                <div class="form-group">
                    <label for="statementArtist">Statement <span class="span-form-required">*</span></label>
                    <textarea name="statementArtist" id="statementArtist" cols="30" rows="10" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label for="facebookArtist">Facebook</label>
                    <input type="text" name="facebookArtist" id="facebookArtist" class="form-control">
                </div>
                <div class="form-group">
                    <label for="instagramArtist">Instagram</label>
                    <input type="text" name="instagramArtist" id="instagramArtist" class="form-control">
                </div>
                <div class="form-group">
                    <label for="pictureArtist">Profile Picture</label>
                    <input type="file" name="pictureArtist" id="picture">
                </div>
            </div>

            <!-- Submit form for both customers and artists. We have to check later whether an artist or a customer is signing up -->
            <button type="submit" class="btn btn-primary" name="signup">Sign Up</button>
        </form>
    </div>

    <script>
        // Shows either the customer form or the artist form based on which radio button has been selected
        function showForm(radio) {
            const artist = document.querySelector(".artist");
            const customer = document.querySelector(".customer");

            if(radio.value === "customer") {
                artist.style.display = "none";
                customer.style.display = "block";
            }
            else if(radio.value === "artist") {
                customer.style.display = "none";
                artist.style.display = "block";
            }
        }
    </script>
</body>
</html>