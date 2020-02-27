<?php


function game_savecookie($_cookieprefix , $_value = null)
{
	//Run Json Encode
	$_value = json_encode($_value);

	//Save Cookie
	setcookie($_cookieprefix , $_value , time() + (86400 + 365));
}


function game_save()
{
	if ($_SESSION['save'] === true) 
	{
		return false;
	}
	$_SESSION['game_time_end'] = time();
	$_SESSION['save'] = true;
	game_savecookie('game_save');

	//Save Game Total Result
	Game_saveresult();

	//Save For Each Player
	Game_saveresult($_SESSION['playernames']['X'] , true);
	Game_saveresult($_SESSION['playernames']['O'] , true);

	//Save For Two Player
	Game_saveresult($_SESSION['playernames']['X'] .'-'.$_SESSION['playernames']['O']);
}


function Game_saveresult($_player = null , $single = false)
{
	$_cookieprefix = 'game_datail';

	if($_player)
	{
		$_cookieprefix.= '_' . $_player;
	}

	$newvalue = [];
	$detail_list =
	[
		'count' ,
		'win' ,
		'lose' ,
		'draw' ,
		'Resign' ,
		'inprogress' ,
		'total_time' ,
		'total_move',
		'total_move_win'
	];

	// Read Cookie If Exist
	if (isset($_COOKIE[$_cookieprefix]))
	{
		$newvalue = json_decode($_COOKIE[$_cookieprefix] , true);
	}

	$newvalue['player'] = $_player ;

	// If Cookie Doesnt Exist Save Zero As Default Value
	foreach ($detail_list as $value)
	{
		if (!isset($newvalue[$value]))
		{
			$newvalue[$value] = 0;
		}
	}

	// Increase Count ++
	$newvalue['count'] = $newvalue['count'] + 1;

	$game_has_winner = check_winner();

	//IF Has Winner
	if ($game_has_winner)
	{
		//If This Player Is Winner
		if ($_SESSION['playernames'][$game_has_winner] == $_player)
		{
			$newvalue['win'] = $newvalue['win'] +1;
		}
		//Else Is This Player is Looser
		elseif ($single)
		{
			$newvalue['lose'] = $newvalue['lose'] +1;
		}
		//Else For Two Player Together
		elseif ($single === false) 
		{
			if (!isset($newvalue['win_' . $game_has_winner]))
			{
				$newvalue['win_' . $game_has_winner] = 0;
			}
			$newvalue['win_' . $game_has_winner] = $newvalue['win_' . $game_has_winner] +1;
		}
		//Else For Total
		else
		{
			$newvalue['win'] = $newvalue['win'] +1;
			unset($newvalue['lose']);
		}
	}
	//Else if Draw
	elseif ($game_has_winner === false)
	{
		$newvalue['draw'] = $newvalue['draw'] + 1;
	}
	elseif ($single &&  isset($_SESSION['status']) && $_SESSION['status'] === 'Resign')
	{
		if ($_SESSION['playernames'][$_SESSION['current']] == $_player)
		{
			$newvalue['Resign'] = $newvalue['Resign'] +1;
		}
		else
		{
			$newvalue['win'] = $newvalue['win'] +1;
		}
	}
	else
	{
		$newvalue['inprogress'] = $newvalue['inprogress'] +1;
	}

	//Save Total Time
	$newvalue['total_time'] = $newvalue['total_time'] + ($_SESSION['game_time_end'] - $_SESSION['game_time_start']);

	//Save Total Move
	$newvalue['total_move'] = $newvalue['total_move'] + ($_SESSION['game_move_X'] + $_SESSION['game_move_O']);

	//Save Total Move Win
	if ($game_has_winner)
	{
		$newvalue['total_move_win'] = $newvalue['total_move_win'] + ($_SESSION['game_move_' . $game_has_winner]);
	}
	//if Save For Single Player
	if ($single)
	{
		$newvalue['type'] = 'single';
	}
	//if Save For Two Player
	elseif($_player)
	{
		$newvalue['type'] = 'against';
	}
	//If Save For Total
	else
	{
		$newvalue['type'] = 'total';
	}
	if ($newvalue['player'] === null)
	{
		unset($newvalue['player']);
	}

	//Save Result
	game_savecookie($_cookieprefix , $newvalue);
}



?>