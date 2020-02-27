<?php


function game_setname()
{

	if (isset($_POST['setname']))
	{
		$p1 = trim($_POST['Player1']);
		$p2 = trim($_POST['Player2']);

		$_SESSION['playernames'] = [ 'X' => $p1 , 'O'=> $p2 ];
		$_SESSION['lastwinner'] = null;
		header("Location:" .CURRENT_URL);
	}
	$el = null;
	$el .= '<form method="post" id="game">';
	$el .= "<input class='inp' id='inp1' value=' ". $_SESSION['playernames']['X'] ."' type='text' name='Player1'  placeholder='Player1' />";
	$el .= "<input class='inp'id='inp2' value=' ". $_SESSION['playernames']['O'] ."' type='text' name='Player2'  placeholder='Player2' />";
	$el .= "<input id='btn1' type='submit' name='setname' value='Save Names' />";
	$el .= "<a id='btn2' href='".CURRENT_URL."'>Return</a>";
	$el .= '</form>';

	return $el;
}


function game_resetBtn()
{
	$result = null;
	$reset_value = 'Start';
	$resetName = 'restart';

	if ($_SESSION['status'] == 'inprogress')
	{
		$reset_value = 'Resign';
		$resetName = 'Resign';
	}
	elseif ($_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw')
	{
		$reset_value = 'Play Again!';
	}
	if ($_SESSION['status'] !== 'awaiting')
	{
		$result = "<input id='reset' type='submit' name='$resetName' value='$reset_value'>" ;
	}

	return $result;
}


function game_create()
{
	$element = null;
	$element .= "<div  class='f title'>";
	$element .= "<div  class='f' id='div1'>";


	if ($_SESSION['status'] == 'awaiting')
	{
		$element .= "<a id='pwyn' href='?action=SetName'>Change Player Names</a>";
	}
	else
	{
		$lastwinner =['X' => null , 'O'=>null];
		$cup = '<img id="trophy" src="trophy_1.svg">';

		if ($_SESSION['lastwinner'] === 'O')
		{
			$lastwinner['O'] = $cup;
		}
		elseif($_SESSION['lastwinner'] === 'X')
		{
			$lastwinner['X'] = $cup;
		}
		$element .= "<div class='c5 ". game_activeChecker('X'). "' id='player1'>" .$lastwinner['X']. $_SESSION['playernames']['X'] ."(<span class='cX' id='cX'>X</span>)</div>";
		$element .= "<div class='c2'>Draw</div>";
		$element .= "<div class='c5 ". game_activeChecker('O'). "' id='player2'>". $_SESSION['playernames']['O'] ."(<span class='cO' id='cO'>O</span>)" .$lastwinner['O']. "</div>";
	}
	$element .= "</div>";
	$element .= "<div class='f' id='div2'>";
	$element .= "<div class='c5'>". game_playerHistory('win_X') . "</div>";
	$element .= "<div class='c2'>". game_playerHistory('draw') ."</div>";
	$element .= "<div class='c5'>". game_playerHistory('win_O') ."</div>";
	$element .= "</div>";
	//Close Main Row
	$element .= "</div>";

	$element .= '<form method="post" id="game">';
	foreach ($_SESSION['game'] as $cell => $value)
	{
		$classname = null;
		if ($value)
		{
			$classname = 'c'.$value;
		}
		$element .= "          <input type='submit' class='cell $classname' id='cell$cell' value='$value' name='cell$cell'";
		if ($value)
		{
			$element .= "disabled";
		}
		elseif ($_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw')
		{
			$element .= "disabled";
		}
		$element .= ">\n";
	}

	$element .= game_resetBtn();
 	if ($_SESSION['status'] !== 'inprogress')
	{
		$element .= "</br> <a id='sr' href='".CURRENT_URL."?action=showResult'>Show Result</a>";
		if ($_SESSION['playernames']['O'] === 'Computer')
		{
			$element .= "</br> <a id='sr' href='".CURRENT_URL."?action=MultiPlayer'>Multi Player</a>";
		}
		else
		{
			$element .= "</br> <a id='sr' href='".CURRENT_URL."?action=SinglePlayer'>Single Player</a>";	
		}	
	}

	$element .= '</form>';

	return $element;
}

?>