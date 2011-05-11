<?php

/*
  MIT License
  Copyright (c) 2011 Peter Petermann

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation
  files (the "Software"), to deal in the Software without
  restriction, including without limitation the rights to use,
  copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the
  Software is furnished to do so, subject to the following
  conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  OTHER DEALINGS IN THE SOFTWARE.

 */

/**
 * Abstract class for easier implementation of singletons
 *
 * @author Georg Grossberger
 * @package Kingboard
 */
abstract class Kingboard_Helper_Singleton implements King23_Singleton {

    private static $instance;
    
    /**
     * Implement the singleton
     * 
     * @return Kingboard_Helper_Singleton 
     */
    public static function getInstance()
    {
        if (!static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    /**
     * Enforce the singleton
     */
    
    private function __construct() {}
    private function __clone() {}
}
