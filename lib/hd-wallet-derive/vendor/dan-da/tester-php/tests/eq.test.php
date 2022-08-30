<?php

namespace tester;

class eq extends test_base {

    public function runtests() {
        $this->test1();
    }
    
    protected function test1() {
        
        $this->eq( 1, "1.00", 'one' );
        $this->eq( 2, "2.00", 'two' );
    }

}
