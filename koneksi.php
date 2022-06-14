<?
    $servername = ""; 
    $usernamedb = ""; 
    $password = "";
    $dbname = "";
                
    // Create connection
    $conn = mysqli_connect($servername, $usernamedb, $password, $dbname);
    // Check connection
    if (!$conn) {
     die("Connection failed: " . mysqli_connect_error());
    }
    
    //RUN FIRST
    //https://api.telegram.org/bot1182136404:AAEQzSbAUpLfJFJA7uNO89nfIyLEBpFvYak/setWebhook?url=[URL bot.php]
?>