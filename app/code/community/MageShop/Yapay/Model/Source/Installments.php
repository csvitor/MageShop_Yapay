<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to suporte@tray.net.br so we can send you a copy immediately.
 *
 * @category   Tray
 * @package    Tray_CheckoutApi
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageShop_Yapay_Model_Source_Installments
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=> "1x"),
            array('value' => 2, 'label'=> "2x"),
            array('value' => 3, 'label'=> "3x"),
            array('value' => 4, 'label'=> "4x"),
            array('value' => 5, 'label'=> "5x"),
            array('value' => 6, 'label'=> "6x"),
            array('value' => 7, 'label'=> "7x"),
            array('value' => 8, 'label'=> "8x"),
            array('value' => 9, 'label'=> "9x"),
            array('value' => 10, 'label'=> "10x"),
            array('value' => 11, 'label'=> "11x"),
            array('value' => 12, 'label'=> "12x"),
        );
    }
}