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
            $_SESSION["logged"]=true;
            $_SESSION["user"]=$username;
            $this->conn->close();
            // redirect to home page
            header("Location: ../view/profile.php");
            exit();
        } else {
            // authentication failed, display an error message
            $_SESSION["logged"]=false;
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
        
        // validate data form
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {             
            $_SESSION['error'] = "Invalid email format";
            // redirect to register page
            header("Location: ../view/register.php");
            exit();
        }
        
        // insert to database
        $stmt = $this->conn->prepare("INSERT INTO Users (name, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $email);
        $stmt->execute();
        $this->conn->close();
        // authentication successful
        $_SESSION["logged"]=true;
        $_SESSION["user"]=$username;
        // redirect to register page
        header("Location: ../view/home.php");
        exit();
    }
}
