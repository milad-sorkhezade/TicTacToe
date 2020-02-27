<?php


function game_start($_lastgame = null)
{
    if ($_lastgame && is_array($_lastgame))
    {
        $_SESSION['game'] = $_lastgame['game'];
        $_SESSION['playernames'] = $_lastgame['playernames'];
        $_SESSION['lastwinner'] = $_lastgame['lastwinner'];
        $_SESSION['game_move_X'] = $_lastgame['game_move_X'];
        $_SESSION['game_move_O'] = $_lastgame['game_move_O'];
    }
    else
    {
        $_SESSION['game'] =
        [
            1 => null,
            2 => null,
            3 => null,
            4 => null,
            5 => null,
            6 => null,
            7 => null,
            8 => null,
            9 => null,
        ];
        $_SESSION['game_move_X'] = 0;
        $_SESSION['game_move_O'] = 0;
    }
    
    $_SESSION['status'] = 'awaiting';
    $_SESSION['save'] = false;

    if (!isset($_SESSION['playernames']))
    {
        $_SESSION['playernames'] = [ 'X' => 'Player1' , 'O' => 'Player2'];
    }
    if (isset($_SESSION['lastwinner']))
    {
        $_SESSION['current'] = $_SESSION['lastwinner'];
    }
    else
    {
        $randplayer                 = array_rand($_SESSION['playernames'] , 1);
        $_SESSION['current']        = $randplayer;
        $_SESSION['lastwinner']     = null;
    }
    //Save Start Time
    $_SESSION['game_time_start'] = time();
}


function game_turn()
{
    if($_SESSION['status'] == 'win' || $_SESSION['status'] == 'draw')
    {
        return null;
    }

    foreach ($_SESSION['game'] as $cell => $value)
    {
        if (isset($_POST['cell' .$cell]))
        {
            $_SESSION['status'] = 'inprogress';
            if ($_SESSION['game'][$cell]== null)
            {
                $_SESSION['game'][$cell]= $_SESSION['current'];
                if ($_SESSION['current'] ==='X')
                {
                    $_SESSION['game_move_X'] = $_SESSION['game_move_X'] + 1;
                    $_SESSION['current'] = 'O';
                }
                else
                {
                    $_SESSION['game_move_O'] = $_SESSION['game_move_O'] + 1;
                    $_SESSION['current'] = 'X' ;
                }
            }
        }
    }

    if ($_SESSION['status'] === 'inprogress') 
    {
    
        $game_save_array =
        [
            'game'          => $_SESSION['game'],
            'playernames'   => $_SESSION['playernames'],
            'game_move_X'   => $_SESSION['game_move_X'],
            'game_move_O'   => $_SESSION['game_move_O'],
            'lastwinner'    => $_SESSION['lastwinner']
        ];
        game_savecookie('game_save' , $game_save_array);
    }
}


function game_computerMove()
{
    $emptycells = null;

    foreach ($_SESSION['game'] as $cell => $value)
    {
        if (!$value)
        {
            $emptycells[] = $cell;
        }
    }
    $computerMove               = array_rand($emptycells , 1);
    $computerMove               = $emptycells[$computerMove];
    return $computerMove;
}



function check_winner()
{
    $g = $_SESSION['game'];
    $winner = null ;

    if
        (
        ($g[1] && $g[1] == $g[2] && $g[2] == $g[3])
        ||($g[4] && $g[4] == $g[5] && $g[5] == $g[6])
        ||($g[7] && $g[7] == $g[8] && $g[8] == $g[9])

        ||($g[1] && $g[1] == $g[4] && $g[4] == $g[7])
        ||($g[2] && $g[2] == $g[5] && $g[5] == $g[8])
        ||($g[3] && $g[3] == $g[6] && $g[6] == $g[9])

        ||($g[1] && $g[1] == $g[5] && $g[5] == $g[9])
        ||($g[3] && $g[3] == $g[5] && $g[5] == $g[7])
        )
    {
        if ($_SESSION['current'] == 'X')
        {
            $winner = 'O';
        }
        else
        {
            $winner = 'X';
        }
    }
    elseif (!in_array(null, $g))
    {
        $winner = false;
    }
    return $winner;
}


function game_winner()
{
    if ($_SESSION['status'] === 'awaiting')
    {
        return null;
    }

    $game_result = check_winner();
    $result = null;
    $changename =null;

    if ($game_result)
    {
        if (game_checkNameChanged())
        {
            Game_save();
        }
        else
        {
            $changename = "<p><a id='result2' href='?action=SetName'>Do You Want Save Your Name?</a></p>";
        }
        //Has One Winner
        $_SESSION['lastwinner'] = $game_result;
        $game_result = $_SESSION['playernames'][$game_result];
        $_SESSION['status'] = 'win';
        $result = "<div id='result1'>$game_result Win! $changename</div>";
    }
    elseif ($game_result === false)
    {
        if (game_checkNameChanged())
        {
            Game_save();
        }
        else
        {
            $changename = "<p><a id='result2' href='?action=SetName'>Do You Want Save Your Name?</a></p>";
        }
        //Draw
        $_SESSION['lastwinner'] = null;
        $_SESSION['status'] = 'draw';
        $result = "<div id='result1'>Draw! $changename</div>";
        Game_save();
    }
    else{
        $_SESSION['status'] = 'inprogress';
    }
    return $result;
}


function game_playerHistory($_needle)
{
    $cookieName = 'game_datail_' . $_SESSION['playernames']['X'] .'-'.$_SESSION['playernames']['O'];
    $history = null ;
    if(isset($_COOKIE[$cookieName]))
    {
        $history = $_COOKIE[$cookieName];
        $history = json_decode($history , true);
    }
    if (isset($history[$_needle]))
    {
        $history = $history[$_needle];
    }
    else
    {
        $history = '-';
    }
    return $history;
}

?>