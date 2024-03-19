<?php
session_start();

// check if form is submitted
$user = new UserController();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // check button
    if (isset($_POST["login"])) {
        echo "<p>Login button is clicked.</p>";
        $user->login();
    }
    if (isset($_POST["logout"])) {
        echo "<p>Logout button is clicked.</p>";
        $user->logout();
    }
    if (isset($_POST["register"])) {
        echo "<p>Register button is clicked.</p>";
        $user->register();
    }
}

class UserController
{
    private $conn;

    public function __construct()
    {
        // database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "dam1m05uf3p1";

        // create connection
        $this->conn = new mysqli($servername, $username, $password, $dbname);

        // check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        echo "Connected successfully";
    }

    /**
     * login user to application
     */
    public function login(): void
    {
        $username = $_POST["username"];
        $password = $_POST["password"];
        // check against a database
        $stmt = $this->conn->prepare("SELECT name, password FROM users  WHERE name=? AND password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();

        if ($stmt->fetch()) {
            // authentication successful
            $_SESSION["logged"] = true;
            $_SESSION["user"] = $username;
            $this->conn->close();
            // redirect to home page
            header("Location: ../view/profile.php");
            exit();
        } else {
            // authentication failed, display an error message
            $_SESSION["logged"] = false;
            $_SESSION['error'] = "Invalid username or password.";
            $this->conn->close();
            // redirect to login
            header("Location: ../view/login.php");
        }
    }

    /**
     * logout user from application
     */
    public function logout(): void
    {
        // clean variables
        unset($_SESSION["logged"]);
        unset($_SESSION["user"]);
        // destro session
        session_destroy();
        // redirect to index page
        header("Location: ../view/index.php");
    }

    /**
     * register user to application
     */
    public function register(): void
    {
        // get data from form
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];
        $nameImage = $_FILES['image']['name'];
        $typeImage = $_FILES['image']['type'];
        $sizeImage = $_FILES['image']['size'];
        // check image exist and size
        if (!empty($nameImage) && ($sizeImage <= 2000000)) {
            //check format
            if (($typeImage == "image/jpeg")
                || ($typeImage == "image/jpg")
                || ($typeImage == "image/png")
            ) {
                // path to save images
                $target_dir = "../view/img/";
                // define folder + name of file
                $target_file = $target_dir . basename($nameImage);
                // move image from temporal folder to image server folder
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    echo "uploading";
                } else {
                    //in case any error moving to server
                    $_SESSION['error'] = "Error uploading";
                    $_SESSION['error'] = $target_file;
                    // redirect to register page
                    header("Location: ../view/register.php");
                }
                
            } else {
                //in case any error in format image
                $_SESSION['error'] = "Invalid image format";
                // redirect to register page
                header("Location: ../view/register.php");
            }
        } else {
            //in case error in size image
            if ($nameImage == !NULL) {
                $_SESSION['error'] = "Invalid image size";
                // redirect to register page
                header("Location: ../view/register.php");
                exit();
            }
        }       

        // validate data form
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format";
            // redirect to register page
            header("Location: ../view/register.php");
            exit();
        }

        // insert to database
        $stmt = $this->conn->prepare("INSERT INTO Users (name, password, email, path_img) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $email, $nameImage);
        
        try {
            //code...
            if ($stmt->execute()) {
                echo "New record created successfully";
                // register successful
                $_SESSION["logged"] = true;
                $_SESSION["user"] = $username;
                $_SESSION["image"] = $nameImage;
                $this->conn->close();
                // redirect to home page
                header("Location: ../view/home.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid register";
                $this->conn->close();
                // redirect to register page
                header("Location: ../view/register.php");
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->conn->close();
            // redirect to register page
            header("Location: ../view/register.php");
            //throw $th;

        } finally {
            // Close connection
            $this->conn->close();
        }
    }
}

