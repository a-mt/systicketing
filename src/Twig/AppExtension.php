<?php
namespace App\Twig;

class AppExtension extends \Twig_Extension
{
    public function getOperators()
    {
        return array(
            array(), // unary operators
            array(
                '===' => array(
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_StrictEqual',
                    'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT
                )
            ), // binary operators
        );
    }
}

/**
 * Add the === operator to Twig
 */
class StrictEqualBinary extends \Twig_Node_Expression_Binary
{
    public function operator(\Twig_Compiler $compiler)
    {
        return $compiler->raw('===');
    }
}
class_alias('App\Twig\StrictEqualBinary', 'Twig_Node_Expression_Binary_StrictEqual', false);
