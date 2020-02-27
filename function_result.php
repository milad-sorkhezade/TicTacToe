<?php


function game_showResult($type = 'single')
{
	//Get Game Result
	$datatable = game_getresult();
	if (!$datatable) 
	{
		return null;
	}

	$fields = $datatable[key($datatable)];
	$fields = array_keys($fields);

	$result = null;

	$result .= ' <ol id="resulttable">';
	$result .= ' <div class="t_title">';

	$ascParam = null;
	if (!isset($_GET['asc']))
	{
		$ascParam = '&asc=true';
	}

	//Draw Table Title
	foreach ($fields as $key => $fieldName)
	{
		$result .= "<span class='f_$fieldName'><a href='?action=showResult&by=$fieldName$ascParam'>".ucwords(str_replace('_' , ' ', $fieldName))."</a></span>";
	}

	$result .= '</div>';

	//Draw Table Data
	foreach ($datatable as $playerName => $value)
	{
		$result .= '<li>';
		foreach ($fields as $key => $fieldName)
		{
			if ($fieldName === 'rank')
			{
				$value[$fieldName] = game_getRankName($value[$fieldName]);
			}
			$result .= "<span class='f_$fieldName'>".$value[$fieldName]."</span>";
		}

		$result .= '</li>';
	}

	$result .= '</ol>';
	$result .= "<nav class='nav'>";
	$result .= "<a id='tsa' href='".CURRENT_URL."?action=showResult&&type=total'>Total</a>";
	$result .= "<a id='tsa' href='".CURRENT_URL."?action=showResult&&type=against'>VS.</a>";
	$result .= "<a id='tsa' href='".CURRENT_URL."?action=showResult&&type=single'>Single</a>";
	$result .= "</nav>";


	$result .= "<div class='return'><span id='return'><a href='".CURRENT_URL."'>Return</a></span></div>";

	return $result;
}


function game_getresult($type = 'single')
{	
	$result = null;
	//If User Wants Custom Type Show It
	if (isset($_GET['type']))
	{
		$type = $_GET['type'] ;
	}

	foreach ($_COOKIE as $key => $value)
	{
		if (strpos($key, 'game_datail') !==false  )
		{
			//Decode Cookie Value
			$value = json_decode($value , true);
			if (isset($value['type']) && $value['type'] == $type)
			{
				//If Player Name Is Not Set use Total
				if (!isset($value['player']))
				{
					$value['player'] = 'Total';
				}

				$point 			= $value['win']*3 + $value['draw']*1;
				$avg_time 		= $value['total_time'] / $value['count'];
				$avg_time_move 	= $value['total_time']/ $value['total_move'];
				$avg_move_win 	=  '-';

				if ($value['win'] > 0)
				{
					$avg_move_win =  $value['total_move_win']/ $value['win'];
				}


				$result[$value['player']] =
				[
					'player'		=> $value['player'],
					'count' 		=> $value['count'],
					'win' 			=> $value['win'],
					'lose' 			=> $value['lose'],
					'draw' 			=> $value['draw'],
					'Resign' 		=> $value['Resign'],
					'inprogress' 	=> $value['inprogress'],
					'avg_time' 		=> round($avg_time , 0),
					'avg_move_win' 	=> round($avg_move_win, 0),
					'avg_time_move' => round($avg_time_move , 0),
					'point' 		=> $point,
					'rank' 			=> null,
				];
			}
		}
	}

	$result = game_getrank($result);

	$result = game_sortResult($result);
	return $result;
}

function game_sortResult($_datatable ,$_by = null , $_desc = null)
{
	//Get Input Value From User in get
	if ($_by === null)
	{
		if (isset($_GET['by'])) 
		{
			$_by = $_GET['by'] ;
		}
	}
	else
	{
		$_by = 'point';
	}
	//Get Input Value From User in get
	if ($_desc === null)
	{
		if (isset($_GET['asc']))
		{
			$_desc = false;
		}
	
		else
		{
			$_desc = true;
		}
	}	

	$datatable_filterd = array_column($_datatable, $_by , 'player');
	$datatable_filterd = array_filter($datatable_filterd);
	if ($_desc)
	{
		//Sort Array Descending
		arsort($datatable_filterd);
	}
	else
	{
		//Sort Array Ascending
		asort($datatable_filterd);
	}
	$_datatable = array_merge($datatable_filterd , $_datatable);

	return $_datatable ; 
}


/**
*Calculate Rank Of Player
*Gold		1	
*Silver 	2
*Bronze 	3
*/
function game_getrank($_datatable)
{
	$_datatable = game_sortResult($_datatable , 'point' , true);
	$datatable_filterd = array_column($_datatable, 'point' , 'player');
	$counter = 0;
	$silver = null;
	$bronze = null;

	foreach ($datatable_filterd as $playerName => $point)
	{
		if ($counter <= count($datatable_filterd) * 0.05)
		{
			//Gold
			$_datatable[$playerName]['rank'] = 1 ;
		}
		elseif ($counter <= count($datatable_filterd) * 0.15 || !$silver)
		{
			//Silver
			$_datatable[$playerName]['rank'] = 2 ;
			$silver = true;
		}
		elseif ($counter <= count($datatable_filterd) * 0.30 || !$bronze)
		{
			//Bronze
			$_datatable[$playerName]['rank'] = 3 ;
			$bronze = true;
		}
		else
		{
			$_datatable[$playerName]['rank'] = 4 ;

		}
		//count($datatable_filterd);
		$counter++;
	}
	$_datatable = game_updateRank($_datatable , 'avg_time' , false);
	$_datatable = game_updateRank($_datatable , 'avg_move_win' , false);
	$_datatable = game_updateRank($_datatable , 'avg_time_move' , false);
	
	return $_datatable;
}


function game_updateRank($_datatable , $_field)
{
	$_datatable = game_sortResult($_datatable , $_field , false);
	$datatable_filterd = array_column($_datatable, $_field , 'player');
	$counter = 0;

	foreach ($datatable_filterd as $playerName => $value)
	{
		if ($counter <= count($datatable_filterd) * 0.05)
		{
			//Improve One Level By Decreasing Number
			$_datatable[$playerName]['rank'] = $_datatable[$playerName]['rank'] - 1;
		}
		$counter++;
	}

	return $_datatable;
}


function game_getRankName($_rank)
{
	if ($_rank <= 0) 
	{
		$_rank = '<img id="rank" src="gold.png">';
	}
	elseif ($_rank ==1) 
	{
		$_rank = '<img id="rank" src="gold.png">';
	}
	elseif ($_rank ==2) 
	{
		$_rank = '<img id="rank" src="silver.png">';
	}
	elseif($_rank ===3)
	{
		$_rank = '<img id="rank" src="bronze.png">';
	}
	else
	{
		$_rank =' - ';
	}

	return $_rank;
}
?>