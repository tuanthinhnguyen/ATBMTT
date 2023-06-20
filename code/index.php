<?php

$title = NULL;
$message = NULL;
$errors = [];
$success = false;

try{
$db = new PDO("mysql:host=127.0.0.1;dbname=selfdestroying", "root", "");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

if(!empty($_POST)){
   if(!empty($_POST['title'])){ $title = htmlentities($_POST['title']); } else { $errors['Title'] = 'Title is a required field'; }
   if(!empty($_POST['message'])){ $message =htmlentities($_POST['message']); } else {$errors['Message'] = 'Message is a required field'; }

if(!count($errors)){

    $random = random_bytes(15);
    $hash = bin2hex($random);

    $stmt = $db->prepare("INSERT INTO messages (title, message, hash) VALUES (:title, :message, :hash)");
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':hash', $hash);
    $stmt = $stmt->execute();

    if($stmt) $success = true;

} else {
   print_r($errors);
   }

} elseif(isset($_GET['id'])) {
    $hash = htmlentities($_GET['id']);

    $stmt = $db->prepare("SELECT title, message FROM messages WHERE hash = :hash LIMIT 1");
    $stmt->bindParam(':hash', $hash);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $count = $stmt->rowCount();

    if($count){

      ($stmt = $db->prepare("DELETE FROM messages WHERE hash = ?"))->execute([$hash]);
      $deleted = $stmt->rowCount();

      $title = $result['title'];
      $message = $result['message'];
    } else {
        $title = 'Sorry :(';
        $message = "It appears that hash is either not in the database or has already been removed";
    }
}

?>

<!DOCTYPE html>
<html>
     <head>
        <meta charset="utf-8">
        <title>Self destroying message app</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">
    </head>
    <body>

        <div class="container">
            <?php if(isset($_GET['id'])) {?>
              <h1><?=$title?></h1>
              <p class='lead'><?=$message?></p>
            <?php if(isset($deleted)) {?><small>This message has been removed from the system.</small><?php }?>
            <?php } else { ?>

            <?php if(!$success) { ?>
        
        <h1>Self destroying message app</h1>

            <form action='' method='POST'>
                <p><input type="text" class="form-control" placeholder="Title..." name="title" values='<?=$title?>'></p>
                <p><textarea class="form-control" rows="3" placeholder="Please enter a message" name="message"><?=$message?></textarea></p>
                <p><input type="text" class="form-control" placeholder="Gmail..." name="gmai" values='<?=$title?>'> </p>
                <p><button class="btn btn-default" type="submit">Submit</button></p>
            </form>

            <?php } else { ?>

             <h1>success</h1>
             <p>Your message has been successfully added to the database</p>
             <a href="/?id=<?=$hash?>">View your message here</a>
            <?php } ?>
          <?php } ?>
        </div>
    </body>
</html>
