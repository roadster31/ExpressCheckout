<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace ExpressCheckout\Loop;

use ExpressCheckout\Model\ExpressCheckoutCustomerQuery;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

/**
 * @author  Franck Allimant <franck@cqfdev.fr>
 */
class IsExpressCustomer extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('customer_id', null, true)
        );
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var \ExpressCheckout\Model\ExpressCheckoutCustomer $expressCheckoutCustomer */
        foreach ($loopResult->getResultDataCollection() as $expressCheckoutCustomer) {
            $loopResultRow = new LoopResultRow();

            $loopResultRow
                ->set('ID', $expressCheckoutCustomer->getId())
                ->set('CUSTOMER_ID', $expressCheckoutCustomer->getCustomerId())
            ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

    /**
     * this method returns a Propel ModelCriteria
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria()
    {
        return ExpressCheckoutCustomerQuery::create()
            ->filterByCustomerId($this->getCustomerId())
            ;
    }
}
