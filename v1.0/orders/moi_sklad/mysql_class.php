<?php

final class MySQL
{

    private $link;

    public function __construct($hostname, $username, $password, $database)
    {
        if (!$this->link = mysqli_connect($hostname, $username, $password, $database)) {
            trigger_error('Error: Could not make a database link using ' . $username . '@' . $hostname);
        }

        if (!mysqli_connect($hostname, $username, $password, $database)) {
            trigger_error('Error: Could not connect to database ' . $database);
        }

        mysqli_query($this->link, "SET NAMES 'utf8'");
        mysqli_query($this->link, "SET CHARACTER SET utf8");
        mysqli_query($this->link, "SET CHARACTER_SET_CONNECTION=utf8");
        mysqli_query($this->link, "SET SQL_MODE = ''");
    }

    public function query($sql)
    {
        if ($this->link) {
            $resource = mysqli_query($this->link, $sql);

            if ($resource) {
                if (is_object($resource)) {
                    $i = 0;

                    $data = array();

                    while ($result = mysqli_fetch_assoc($resource)) {
                        $data[$i] = $result;

                        $i++;
                    }

                    mysqli_free_result($resource);

                    $query = new stdClass();
                    $query->row = isset($data[0]) ? $data[0] : array();
                    $query->rows = $data;
                    $query->num_rows = $i;

                    unset($data);
                    return $query;
                } else {
                    return true;
                }
            } else {
                trigger_error('Error: ' . mysqli_error($this->link) . '<br />Error No: ' . mysqli_errno($this->link) . '<br />' . $sql);

                $_result['mysqli_errno'] = mysqli_errno($this->link);
                $_result['mysqli_error'] = mysqli_error($this->link);
                return $_result;
                // dump(mysqli_error($this->link));
                exit();
            }
        }
    }

    public function escape($value)
    {
        if ($this->link) {
            return mysqli_real_escape_string($this->link, $value);
        }
    }

    public function countAffected()
    {
        if ($this->link) {
            return mysqli_affected_rows($this->link);
        }
    }

    public function getLastId()
    {
        if ($this->link) {
            return mysqli_insert_id($this->link);
        }
    }

    public function __destruct()
    {
        if ($this->link) {
            mysqli_close($this->link);
        }
    }
}
