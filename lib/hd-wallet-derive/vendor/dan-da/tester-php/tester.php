#!/usr/bin/env php
<?php

namespace tester;

/*
 * This file implements a very basic test harness.
 */

// be safe and sane.  use strictmode if available via composer.
$autoload_file = __DIR__ . '/vendor/autoload.php';
if( file_exists( $autoload_file )) {
    require_once($autoload_file);
    \strictmode\initializer::init();
}

return exit(main($argv));

abstract class test_base {
    public $results = array();
    
    abstract public function runtests();
    
    private function backtrace() {
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();

        // Remove first item from backtrace as it's this function which
        // is redundant.
        $trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

        return $trace;
    } 
    
    public function eq( $a, $b, $desc ) {
        $ok = $a == $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'equality',
                      'result' => $ok ? "$a == $b" : "$a != $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }
    
    public function ne( $a, $b, $desc ) {
        $ok = $a != $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'inequality',
                      'result' => $ok ? "$a != $b" : "$a == $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function gt( $a, $b, $desc ) {
        $ok = $a > $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'greatherthan',
                      'result' => $ok ? "$a > $b" : "$a <= $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function lt( $a, $b, $desc ) {
        $ok = $a < $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'greatherthan',
                      'result' => $ok ? "$a < $b" : "$a >= $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }
    
    public function contains( $a, $b, $desc ) {
        $ok = (bool)strstr($a, $b);
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'contains',
                      'result' => $ok ? "$a contains $b" : "$a doesn't contain $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function not_contains( $a, $b, $desc ) {
        $ok = !(bool)strstr($a, $b);
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'does not contain',
                      'result' => $ok ? "$a doesn't contain $b" : "$a contains $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }
    
    public function starts_with( $a, $b, $desc ) {
        $ok = substr($a, 0, strlen($b)) == $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'starts with',
                      'result' => $ok ? "$a starts with $b" : "$a doesn't start with $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function ends_with( $a, $b, $desc ) {
        $ok = substr($a, -strlen($b)) == $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'ends with',
                      'result' => $ok ? "$a ends with $b" : "$a doesn't end with $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function matches( $a, $b, $desc ) {
        $ok = preg_match($b, $a);
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'matches',
                      'result' => $ok ? "$a matches $b" : "$a doesn't match $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function has_key( array $a, $b, $desc ) {
        $ok = array_key_exists($b, $a);
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'has key',
                      'result' => $ok ? "$a has key $b" : "$a doesn't have key $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function count_eq( array $a, $b, $desc ) {
        $ok = count($a) == $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'count',
                      'result' => $ok ? "count($a) == $b" : "count($a) != $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }    

    public function count_gt( array $a, $b, $desc ) {
        $ok = count($a) > $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'count >',
                      'result' => $ok ? "count($a) is > $b" : "count($a) is not > $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }    

    public function count_gte( array $a, $b, $desc ) {
        $ok = count($a) >= $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'count >=',
                      'result' => $ok ? "count($a) is >= $b" : "count($a) is not >= $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }    
    
    
    public function count_lt( array $a, $b, $desc ) {
        $ok = count($a) < $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'count <',
                      'result' => $ok ? "count($a) is < $b" : "count($a) is not < $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }    

    public function count_lte( array $a, $b, $desc ) {
        $ok = count($a) <= $b;
        $a = $this->to_str($a);
        $b = $this->to_str($b);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'count <',
                      'result' => $ok ? "count($a) is <= $b" : "count($a) is not <= $b",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }        
    
    public function is_empty( $a, $desc ) {
        $ok = !(bool)(is_array($a) ? count($a) : $a || $a === 0);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is empty',
                      'result' => $ok ? "$a is empty" : "$a is not empty",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function not_empty( $a, $desc ) {
        $ok = (bool)(is_array($a) ? count($a) : $a || $a === 0);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is not empty',
                      'result' => $ok ? "$a is not empty" : "$a is empty",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function not_null( $a, $desc ) {
        $ok = $a !== null;
        $a = $this->to_str($a);
        
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'not null',
                      'result' => $ok ? "$a is not null" : "$a is null",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function is_null( $a, $desc ) {
        $ok = $a === null;
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is null',
                      'result' => $ok ? "$a is null" : "$a is not null",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function is_int( $a, $desc ) {
        $ok = is_int($a);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is integer',
                      'result' => $ok ? "$a is integer" : "$a is not integer",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function is_float( $a, $desc ) {
        $ok = is_float($a);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is float',
                      'result' => $ok ? "$a is a float" : "$a is not a float",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function is_hex( $a, $desc ) {
        // strip '0x' at start if present.
        if( substr($a, 0, 2) == '0x') {
            $a = substr($a, 2);
        }
        $ok = ctype_xdigit($a);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is hex',
                      'result' => $ok ? "$a is hex" : "$a is not hex",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }    
    
    public function is_string( $a, $desc ) {
        $ok = is_string($a);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is float',
                      'result' => $ok ? "$a is a string" : "$a is not a string",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function is_array( $a, $desc ) {
        $ok = is_array($a);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is array',
                      'result' => $ok ? "$a is an array" : "$a is not an array",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    public function is_object( $a, $desc ) {
        $ok = is_object($a);
        $a = $this->to_str($a);
        $res = array( 'success' => $ok,
                      'desc' => $desc,
                      'assertion' => 'is array',
                      'result' => $ok ? "$a is an objet" : "$a is not an object",
                      'stack' => $this->backtrace() );
        $this->add_result($res);
    }

    protected function to_str($var) {
        if(is_array($var)) {
            return 'array';
        }
        if(is_object($var)) {
            return 'object';
        }
        if($var === null) {
            return 'null';
        }
        if(is_scalar($var) && !is_string($var)) {
            return $var;
        }
        return '"' . addcslashes ( $this->shorten((string)$var), "\\\n" ) . '"';
    }    
    
    protected function shorten($str, $maxlen = 20) {
        if(strlen($str) > $maxlen) {
            $str = substr($str, 0, $maxlen-3) . '...';
        }
        return $str;
    }
    
    protected function add_result($result) {
        $result['failnotes'] = $this->add_failnotes();
        $this->results[] = $result;
    }

    /**
     * override this method to add notes about failure,
     * such as executed commands or whatever.
     * @return array of strings.
     */
    protected function add_failnotes() {
        return [];
    }
}

class test_printer {

    static public function print_status( $testname ) {
        echo "Running tests in $testname...\n";
    }
    
    static public function print_results( $results ) {
        $pass_cnt = 0;
        $fail_cnt = 0;
        foreach( $results as $r ) {
            if( $r['success'] ) {
                echo sprintf( "[pass] %s  |  %s\n", $r['result'], $r['desc'] );
                $pass_cnt ++;
            }
            else {
                $failnotes = @count($r['failnotes']) ? "\n" . implode("\n", $r['failnotes']) : '';
                echo sprintf( "[fail] %s  |  %s\n%s%s\n\n", $r['result'], $r['desc'], $r['stack'], $failnotes );
                $fail_cnt ++;
            }
        }
    
        echo "\n\n";    
        echo sprintf( "%s tests passed.\n", $pass_cnt );
        echo sprintf( "%s tests failed.\n", $fail_cnt );
        echo "\n\n";    
    }
}

function run_test($filename) {

    require( $filename );
    $classname = basename( $filename, '.test.php' );

    test_printer::print_status( $classname );
    $fullclassname = __NAMESPACE__ . '\\' . $classname;
    $testobj = new $fullclassname();
    $testobj->runtests();
    return $testobj->results;
    
}

function main($argv) {

    $results = array();
    
    if( count($argv) > 1 ) {
        $results = run_test( $argv[1] );
    }
    else {
        $tests = glob('./*.test.php' );
        foreach( $tests as $test ) {
            $results = array_merge( $results, run_test( $test ) );
        }
    }
    
    test_printer::print_results( $results );
}


?>
