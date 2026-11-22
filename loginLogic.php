<?php 

	session_start();

	include_once('config.php');

	$_SESSION['id'] = $data['id'];
$_SESSION['role'] = $data['role'];

	if(isset($_POST['submit']))
	{
		
		$username = $_POST['username'];

		
		$password = $_POST['password'];

		
		if (empty($username) || empty($password)) {

			
			echo "Please fill in all fields
			";

		}
		else{

			
			$sql = "SELECT id, emri, username, email, password, role
        FROM users
        WHERE username = :username";

			
			
			

			$selectUser->bindParam(":username", $username);

			
$selectUser = $conn->prepare($sql);
$selectUser->execute([':username' => $username]);
$data = $selectUser->fetch(PDO::FETCH_ASSOC);


			if ($data == false) {
				

				
				echo "The user does not exist
				";
			}else{

		
				if (password_verify($password, $data['password'])) {
					
					$_SESSION['id'] = $data['id'];
$_SESSION['username'] = $data['username'];
$_SESSION['email'] = $data['email'];
$_SESSION['emri'] = $data['emri'];
$_SESSION['role'] = $data['role'];

header("Location: redirect.php");
exit();
				}
				else{
					
					echo "The password is not valid
					";
				}

			}

		}


	}


 ?>