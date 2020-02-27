<?php
//Dont Show The Warnings Please :D
ini_set('display_errors', '0');

//Require Files
require_once "function_game.php";
require_once "function_Save.php";
require_once "function_draw.php";
require_once "function_result.php";

//Start Session
session_start();




function game()
{
	$this_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	$this_url = strtok($this_url, "?");
	define('CURRENT_URL' , $this_url);
	
	$el = null;

	//Start The Game At the First
	if (!isset($_SESSION['status']))
	{
		if (isset($_COOKIE['game_save']) && $_COOKIE['game_save'])
 		{
 			$el 		.="<a href='".CURRENT_URL."?action=new'>Do You Want To Start New Game?</a>";
 			$lastgame 	= json_decode($_COOKIE['game_save'], true);
			game_start($lastgame);
 		}
 		else
 		{
			game_start();
 		}
 	}
 	//Else If User Press Restart Btn
 	elseif (isset($_POST['restart']))
 	{
 		game_save();
 		game_start();
 	}
 	//Else If User Press Resign Btn
 	elseif (isset($_POST['Resign']))
  	{
  		$_SESSION['status'] = 'Resign';
  		game_save();
 		game_start();
  	}
	else
  	{
		game_turn();

  	}

	if (isset($_GET['action']) && $_GET['action'] == 'SetName')
	{
		$el .= game_setname();
	}
	elseif ((isset($_GET['action']) && $_GET['action'] == 'showResult'))
	{
		$el .= game_showResult('single');
	}

	//Single Player
	elseif (isset($_GET['action']) && $_GET['action'] == 'SinglePlayer')
	{
		$_SESSION['playernames']['O'] = 'Computer';
		$_SESSION['lastwinner'] = 'X';
		$_SESSION['status'] = 'new';
 		header("Location:" .CURRENT_URL);
	}

	//Multi Player
	elseif (isset($_GET['action']) && $_GET['action'] == 'MultiPlayer')
	{
		$_SESSION['playernames']['O'] = 'Player2';
		$_SESSION['lastwinner'] = null;
		$_SESSION['status'] = 'new';
 		header("Location:" .CURRENT_URL);
	}

	elseif (isset($_GET['action']) && $_GET['action'] == 'new')
	{
		$_SESSION['status'] = 'new';
		game_save();
 		game_start();
 		header("Location:" .CURRENT_URL);
	}
	else
	{
		$el .= game_winner ();
		$el .= game_create ();
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		if ($_SESSION['status'] === 'inprogress' && $_SESSION['playernames']['O'] == 'Computer')
        {
            $_SESSION['game'][game_computerMove()]= $_SESSION['current'];
            $_SESSION['current'] = 'X';
            $el .= game_winner ();
        }
		header("Location:" .CURRENT_URL);
	}

  	return $el;
}



function game_activeChecker($_player)
{
	if($_SESSION['current'] === $_player)
  	{
  		return 'active';
	}

	return null;
}


function game_checkNameChanged()
{
	if (strtolower($_SESSION['playernames']['X']) !== 'player1' || strtolower($_SESSION['playernames']['O']) !== 'player2')
	{
		return true;
	}

	return false;
}

?>