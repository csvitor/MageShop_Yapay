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
 * to web@tryideas.com.br so we can send you a copy immediately.
 *
 * @category   MageShop
 * @package    MageShop_Yapay
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageShop_Yapay_Model_Source_Logs
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=> "Não gerar log"),
            array('value' => 2, 'label'=> "Gerar log transação"),
            array('value' => 3, 'label'=> "Gerar log de todos os eventos"),
        );
    }
}