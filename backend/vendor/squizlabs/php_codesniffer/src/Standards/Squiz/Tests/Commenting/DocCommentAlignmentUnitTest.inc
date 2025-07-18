<?php
/**
* Some info about the class here
 *
 */
class MyClass
{
    /**
     * Some info about the function here.
     *
     *@return void
     */
    function myFunction() {}
}

/**
  *  Some info about the class here
  *
  */
class MyClass
{
    /**
      *Some info about the function here.
      *
      *  @return void
      */
    function myFunction() {}
}

/**
 * Some info about the class here
  *
*/
readonly class MyClass
{
    /**
     * Some info about the function here.
      *
    * @return void
     */
    function myFunction() {}
}

/** @var Database $mockedDatabase */
/** @var Container $mockedContainer */

function myFunction()
{
    echo 'hi';
    /**
        Comment here.
        */
}

/**
 * Creates a map of tokens => line numbers for each token.
 *
 * Long description with some points:
 *   - one
 *   - two
 *   - three
 *
 * @param array  &$tokens   The array of tokens to process.
 * @param object $tokenizer The tokenizer being used to
 *                          process this file.
 * @param string $eolChar   The EOL character to use for splitting strings.
 *
 * @return void
 */
function myFunction() {}

class MyClass2
{
    /**
       * Some info about the variable here.
     */
    var $x;
}

abstract class MyClass
{
    /**
* Property comment
   */
    readonly public string $prop;
}

/**
 * Some info about the enum here
  *
*/
enum Suits: string
{
    /**
      * Some info about the case here.
       */
    case HEARTS;
}

/** ************************************************************************
 * Example with no errors.
 **************************************************************************/
function example() {}

/**
  *  Some info about the class here.
    */
final class FinalClassWithFinalProp
{
    /**
   *Some info about the property here.
    *
     *  @var int
      */
    final $property = 10;
}

class AsymVisibility {
    /**
    * Stars should be aligned.
   */
    public(set) int $prop = 1;

    /**
      * Stars should be aligned.
       */
    protected(set) int $prop = 1;

    /**
     * Stars should be aligned.
     */
    private(set) int $prop = 1;
}
