<?php
/**
 * Helper functions to reduce time spent writing monotonous/repetative code
 * @author Adrian J. van der Wal
 * @version 1.1 
 */

/**
 * Returns the domain of the current website
 * @return string The domain name from the settings.php file
 */
function get_domain()  
{  
    global $websiteUrl;
    return $websiteUrl;
}

/**
 * Builds a HTML anchor tag for display on the website
 * @param string $link The page name of the anchor - e.g. index.php
 * @param type $text The text to display on the page - e.g. Home
 * @param type $title The title of the anchor
 * @param type $extras Extra options for an anchor
 * @return string Returns a String of HTML which can then be echoed to the page
 */
function anchor($link, $text, $title=false, $extras=false)
{
    $data = '<a href="' . get_domain() . $link . '"';

    if ($title)
    {
        $data .= ' title="' . $title . '"';
    }
    else
    {
        $data .= ' title="' . $text . '"';
    }

    if (is_array($extras))
    {
        foreach($extras as $rule)
        {
            $data .= parse_extras($rule);
        }
    }

    if (is_string($extras))
    {
        $data .= parse_extras($extras);
    }

    $data.= '>';
    $data .= $text;
    $data .= "</a>";
    return $data;
}
/**
 * Parses extras for anchor tags
 * @param type $rule The rule to test
 * @return string 
 */
function parse_extras($rule)
{
    if ($rule[0] == "#")
    {
        $id = substr($rule,1,strlen($rule));
        $data = ' id="' . $id . '"';
        return $data;
    }

    if ($rule[0] == ".")
    {
        $class = substr($rule,1,strlen($rule));
        $data = ' class="' . $class . '"';
        return $data;
    }

    if ($rule[0] == "_")
    {
        $data = ' target="' . $rule . '"';
        return $data;
    }

    if (startsWith($rule,"style"))
    {
        $data = ' ' . $rule;
        return $data;
    }
}

/**
 * Debug prints a nicely formatted dump of the variable
 * @param mixed $var  -- variable to dump
 * @param string $var_name  -- name of variable (optional) -- displayed in printout making it easier to sort out what variable is what in a complex output - retrieved if left blank
 * @param string $indent -- used by internal recursive call (no known external value)
 * @param unknown_type $reference -- used by internal recursive call (no known external value)
 */
function debug($var, $var_name, $indent = NULL, $reference = NULL)
{
    $do_dump_indent = "<span style='color:#666666;'>|</span> &nbsp;&nbsp; ";
    $reference = $reference.$var_name;
    $keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';
    if ($var_name == "")
    {
        echo "<div style='text-align:left; background-color:white; font: 100% monospace; color:black;'>";
        echo "There was no name supplied. Debug failed.";
        echo "</div>";
        return ;
    }
    // So this is always visible and always left justified and readable
    echo "<div style='text-align:left; background-color:white; font: 100% monospace; color:black;'>";

    if (is_array($var) && isset($var[$keyvar]))
    {
        $real_var = &$var[$keyvar];
        $real_name = &$var[$keyname];
        $type = ucfirst(gettype($real_var));
        echo "$indent$var_name <span style='color:#666666'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
    }
    else
    {
        $var = array($keyvar => $var, $keyname => $reference);
        $avar = &$var[$keyvar];

        $type = ucfirst(gettype($avar));
        if($type == "String") $type_color = "<span style='color:green'>";
        elseif($type == "Integer") $type_color = "<span style='color:red'>";
        elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
        elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
        elseif($type == "Resource") $type_color = "<span style='color:black'>";
        elseif($type == "NULL") $type_color = "<span style='color:black'>";

        if(is_array($avar))
        {
            $count = count($avar);
            echo "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#666666'>$type ($count)</span><br>$indent(<br>";
            $keys = array_keys($avar);
            foreach($keys as $name)
            {
                $value = &$avar[$name];
                debug($value, "['$name']", $indent.$do_dump_indent, $reference);
            }
            echo "$indent)<br>";
        }
        elseif(is_object($avar))
        {
            echo "$indent$var_name <span style='color:#666666'>$type</span><br>$indent(<br>";
            foreach($avar as $name=>$value) debug($value, "$name", $indent.$do_dump_indent, $reference);
            echo "$indent)<br>";
        }
        elseif(is_int($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif(is_string($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color\"".htmlentities($avar)."\"</span><br>";
        elseif(is_float($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif(is_bool($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
        elseif (is_resource($avar)) echo "$indent$var_name = <span style='color:#666666'>$type - ".get_resource_type($avar)."()</span><br>";
        elseif(is_null($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
        else echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> ".htmlentities($avar)."<br>";

        $var = $var[$keyvar];
    }

    echo "</div>";
}

/**
 * Tries to execute a mysql query
 * @global string $db_host Database Host Name
 * @global string $db_user Database User Name
 * @global string $db_pass Database Password
 * @global string $db_name Database Table Name
 * @global string $debug_mode Debug Level
 * @param string $query The Query to execute
 * @return boolean/mysql result False if failed - True/mysql result if success
 */
function &perform_mysqli_query($query)
{
    global $db_host, $db_user, $db_pass, $db_name, $debug_mode;

    if (!is_string($query))
    {
        return false;
    }

    try
    {
        $con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);   //connect to DB
        if (!$con) 
        {
            if ($debug_mode)
            {
                echo "Could not connect to database.<br />" . mysqli_error() ."<br />";
            }
            return false;
        }
        else
        {
            //mysqli_select_db($con, );    //select the database
            $ret = mysqli_query($con, $query);       //run our SQL and store the data in $result
            mysqli_close($con); // kill the connection to the db
            return $ret;
        }
    }
    catch (Exception $e)
    {
        if ($debug_mode)
            {
                echo "An Error Occured!<br />" . $e ."<br />";
            }
            return false;
    }
}


/**
 * Convert number of seconds into days, hours, minutes and seconds
 * and return a Human Readable string containing those values
 *
 * @param integer $seconds Number of seconds to parse
 * @return string Human Readable string of days, hours:minutes:seconds
 */
function secondsToDaysHoursMinutesSeconds($seconds)
{
    $days = floor($seconds / (60 * 60 * 24));
    // extract hours 
    $divisor_for_hours = $seconds % (60 * 60 * 24);
    $hours = floor( $divisor_for_hours / (60 * 60));

    // extract minutes
    $divisor_for_minutes = $seconds % (60 * 60);
    $minutes = floor($divisor_for_minutes / 60);

    // extract the remaining seconds
    $divisor_for_seconds = $divisor_for_minutes % 60;
    $seconds = ceil($divisor_for_seconds);

    return dayshoursminutessecondsToHumanReadableString($days, $hours, $minutes, $seconds);
}

/**
 * Returns a Human Readable string of days, hours:minutes:seconds
 * @param int $days Number of Days
 * @param int $hours Number of Hours
 * @param int $minutes Number of Minutes
 * @param int $seconds Number of seconds
 * @return string In format - [day(s), ]hours:minutes:seconds
 */
function dayshoursminutessecondsToHumanReadableString($days, $hours, $minutes, $seconds)
{
    $tmp = "";
    if ($days == 1)
    {
        $tmp.= $days." day, ";
    }
    elseif ($days > 1)
    {
        $tmp.= $days." days, ";
    }
    $tmp.=str_pad($hours, 2, "0", STR_PAD_LEFT);
    $tmp.=":";
    $tmp.=str_pad($minutes, 2, "0", STR_PAD_LEFT);
    $tmp.=":";
    $tmp.=str_pad($seconds, 2, "0", STR_PAD_LEFT);
    return $tmp;
}

function getVisitorIP()
{ 
if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    $TheIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
else $TheIp=$_SERVER['REMOTE_ADDR'];

return trim($TheIp);
}
?>