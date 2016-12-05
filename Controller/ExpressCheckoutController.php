<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

/**
 * Created by Franck Allimant, CQFDev <franck@cqfdev.fr>
 * Date: 04/09/2015 17:18
 */

namespace ExpressCheckout\Controller;

use ExpressCheckout\Event\ExpressCheckoutEvent;
use ExpressCheckout\ExpressCheckout;
use ExpressCheckout\Model\ExpressCheckoutCustomer;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\Event\Address\AddressCreateOrUpdateEvent;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\Customer\CustomerLoginEvent;
use Thelia\Core\Event\Newsletter\NewsletterEvent;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Exception\TheliaProcessException;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Model\CustomerQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\NewsletterQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderPostage;
use Thelia\Module\BaseModule;

class ExpressCheckoutController extends BaseFrontController
{
    public function checkout()
    {
        $formClient = $this->createForm('expresscheckout.customer_express_registration');

        try {
            $form = $this->validateForm($formClient);

            $data = $form->getData();

            // Créer ou modifier un client existant
            $customerEvent = new CustomerCreateOrUpdateEvent(
                $data['title'],
                $data['firstname'], // First name
                $data['lastname'],
                $data['address1'], // address 1,
                $data['address2'], // address 2,
                '', // address 3'',
                '', // telephone
                $data['cellphone'], // cellphone,
                $data['zipcode'], // zip code,
                $data['city'], // city,
                $data['country'],
                $data['email'],
                md5($data['email']), // password
                $this->getSession()->getLang()->getId(),
                null, // reseller
                null, // sponsor
                0, // discount
                null, // company
                null // ref
            );

            $customerEvent
                ->setNotifyCustomerOfAccountCreation(false)
                ->setEmailUpdateAllowed(false);

            if (null === $customer = CustomerQuery::create()->findOneByEmail($data['email'])) {
                $this->dispatch(TheliaEvents::CUSTOMER_CREATEACCOUNT, $customerEvent);

                // Flag this customer as "no account"
                $expressCheckoutCustomer = new ExpressCheckoutCustomer();

                $expressCheckoutCustomer
                    ->setCustomerId($customerEvent->getCustomer()->getId())
                    ->save();

            } else {
                $customerEvent->setCustomer($customer);

                $this->dispatch(TheliaEvents::CUSTOMER_UPDATEPROFILE, $customerEvent);
            }

            $customer = $customerEvent->getCustomer();

            // Create the delivery address, if required
            if (true !== $data['same_address']) {
                $addressEvent = new AddressCreateOrUpdateEvent(
                    'Unused label', // label
                    $data['title_livr'],
                    $data['firstname_livr'],
                    $data['lastname_livr'],
                    $data['address1_livr'],
                    $data['address2_livr'],
                    '', // address 3'',
                    $data['zipcode_livr'],
                    $data['city_livr'],
                    $data['country_livr'],
                    $data['cellphone_livr'],
                    '', // telephone
                    '', // company
                    false // is default
                );

                $addressEvent->setCustomer($customer);

                $this->dispatch(TheliaEvents::ADDRESS_CREATE, $addressEvent);

                $deliveryAddress = $addressEvent->getAddress();
            } else {
                $deliveryAddress = $customer->getDefaultAddress();
            }

            // Newsletter subscription
            if (true === $data['newsletter']) {
                $nlEvent = new NewsletterEvent(
                    $customer->getEmail(),
                    $this->getSession()->getLang()->getLocale()
                );

                $nlEvent
                    ->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                ;

                // Security : Check if this new Email address already exist
                if (null !== $newsletter = NewsletterQuery::create()->findOneByEmail($customer->getEmail())) {
                    $nlEvent->setId($newsletter->getId());
                    $this->dispatch(TheliaEvents::NEWSLETTER_UPDATE, $nlEvent);
                } else {
                    $this->dispatch(TheliaEvents::NEWSLETTER_SUBSCRIBE, $nlEvent);
                }
            }

            // -- Perform customer login -------------------------------------------------------------------------------

            $this->dispatch(TheliaEvents::CUSTOMER_LOGIN, new CustomerLoginEvent($customer));

            // -- Create session Order ---------------------------------------------------------------------------------

            $order = new Order();

            $this->getSession()->setOrder($order);

            $orderEvent = new OrderEvent($order);

            $orderEvent->setDeliveryAddress($deliveryAddress->getId());

            // On utilise le premier module de livraison actif
            if (null === $deliveryModule = ModuleQuery::create()
                ->filterByType(BaseModule::DELIVERY_MODULE_TYPE)
                ->filterByActivate(true)
                ->findOne()) {
                throw new TheliaProcessException($this->getTranslator()->trans(
                    'Unable to find an active delivery module. Please check module configuration.',
                    [],
                    ExpressCheckout::DOMAIN_NAME
                ));
            }

            $orderEvent->setDeliveryModule($deliveryModule->getId());

            /* get postage amount */
            $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);

            $postage = OrderPostage::loadFromPostage(
                $moduleInstance->getPostage($deliveryAddress->getCountry())
            );

            $orderEvent->setPostage($postage->getAmount());
            $orderEvent->setPostageTax($postage->getAmountTax());
            $orderEvent->setPostageTaxRuleTitle($postage->getTaxRuleTitle());

            $this->dispatch(TheliaEvents::ORDER_SET_DELIVERY_ADDRESS, $orderEvent);
            $this->dispatch(TheliaEvents::ORDER_SET_DELIVERY_MODULE, $orderEvent);
            $this->dispatch(TheliaEvents::ORDER_SET_POSTAGE, $orderEvent);
    
            $this->dispatch(ExpressCheckoutEvent::ORDER_READY, $orderEvent);
    
            return $this->generateSuccessRedirect($formClient);

        } catch (FormValidationException $e) {
            $message = $this->getTranslator()->trans(
                "Please check your input: %s",
                [
                    '%s' => $e->getMessage()
                ],
                ExpressCheckout::DOMAIN_NAME
            );
        } catch (\Exception $e) {
            $message = $this->getTranslator()->trans(
                "Sorry, an error occurred: %s",
                [
                    '%s' => $e->getMessage()
                ],
                ExpressCheckout::DOMAIN_NAME
            );
        }

        $formClient->setErrorMessage($message);

        $this->getParserContext()
            ->addForm($formClient)
            ->setGeneralError($message)
        ;

        return $this->generateErrorRedirect($formClient);
    }
}
