<?php
/*************************************************************************************/
/*      Copyright (c) Franck Allimant, CQFDev                                        */
/*      email : thelia@cqfdev.fr                                                     */
/*      web : http://www.cqfdev.fr                                                   */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE      */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace ExpressCheckout\Form;

use ExpressCheckout\ExpressCheckout;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ExecutionContextInterface;
use Thelia\Form\FirewallForm;

class CustomerExpressRegistration extends FirewallForm
{
    protected function buildForm()
    {
        $this->formBuilder
            // Add Email address
            ->add("email", "email", array(
                "constraints" => array(
                    new NotBlank(),
                    new Email()
                ),
                "label" => $this->translator->trans("Email Address", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "email",
                ),
            ))
            // Add Newsletter
            ->add("newsletter", "checkbox", array(
                "label" => $this->translator->trans('I would like to receive the newsletter or the latest news.', [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "newsletter",
                ),
                "required" => false,
            ))

            // Adresse de facturation

            ->add("title", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("Title", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "title",
                ),
            ))
            ->add("firstname", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("First Name", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "firstname",
                ),
            ))
            ->add("lastname", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("Last Name", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "lastname",
                ),
            ))
            ->add("address1", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("Street Address", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "address1",
                ),
            ))
            ->add("address2", "text", array(
                'required' => false,
                "label" => $this->translator->trans("Street Address Line 2", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "address2",
                ),
            ))
            ->add("city", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("City", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "city",
                ),
            ))
            ->add("zipcode", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("Zip code", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "zipcode",
                ),
            ))
            ->add("country", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("Country", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "country",
                ),
            ))
            ->add("cellphone", "text", array(
                "constraints" => array(
                    new NotBlank(),
                ),
                "label" => $this->translator->trans("Cellphone", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "cellphone",
                ),
            ))
            ->add("newsletter", "checkbox", array(
                "label" => $this->translator->trans('I would like to receive the newsletter or the latest news.', [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "newsletter",
                ),
                "required" => false,
            ))

            ->add("same_address", "checkbox", array(
                "label" => $this->translator->trans('Utiliser la même adresse pour la livraison.', [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "same_address",
                ),
                "required" => false,
            ))

            // Adresse de livraison

            ->add("title_livr", "text", array(
                "constraints" => array(
                    new Callback([ "methods" => [ [$this, "notBlankIfSameAddressUnchecked"]]])
                ),
                "label" => $this->translator->trans("Title", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "title_livr",
                ),
            ))
            ->add("firstname_livr", "text", array(
                "constraints" => array(
                    new Callback([ "methods" => [ [$this, "notBlankIfSameAddressUnchecked"]]])
                ),
                "label" => $this->translator->trans("First Name", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "firstname_livr",
                ),
            ))
            ->add("lastname_livr", "text", array(
                "constraints" => array(
                    new Callback([ "methods" => [ [$this, "notBlankIfSameAddressUnchecked"]]])
                ),
                "label" => $this->translator->trans("Last Name", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "lastname_livr",
                ),
            ))
            ->add("address1_livr", "text", array(
                "constraints" => array(
                    new Callback([ "methods" => [ [$this, "notBlankIfSameAddressUnchecked"]]])
                ),
                "label" => $this->translator->trans("Street Address", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "address1_livr",
                ),
            ))
            ->add("address2_livr", "text", array(
                'required' => false,
                "label" => $this->translator->trans("Street Address Line 2", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "address1_livr",
                ),
            ))
            ->add("city_livr", "text", array(
                "constraints" => array(
                    new Callback([ "methods" => [ [$this, "notBlankIfSameAddressUnchecked"]]])
                ),
                "label" => $this->translator->trans("City", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "city_livr",
                ),
            ))
            ->add("zipcode_livr", "text", array(
                "constraints" => array(
                    new Callback([ "methods" => [ [$this, "notBlankIfSameAddressUnchecked"]]])
                ),
                "label" => $this->translator->trans("Zip code", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "zipcode_livr",
                ),
            ))
            ->add("country_livr", "text", array(
                "constraints" => array(
                    new Callback([ "methods" => [ [$this, "notBlankIfSameAddressUnchecked"]]])
                ),
                "label" => $this->translator->trans("Country", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "country_livr",
                ),
            ))
            ->add("cellphone_livr", "text", array(
                "label" => $this->translator->trans("Cellphone", [], ExpressCheckout::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "cellphone_livr",
                ),
                "required" => false,
            ))
        ;
    }


    public function notBlankIfSameAddressUnchecked($value, ExecutionContextInterface $context)
    {
        $data = $context->getRoot()->getData();

        if (false === $data["same_address"] && empty($value)) {
            $context->addViolation(
                $this->translator->trans("Veuillez compléter ce champ", [], ExpressCheckout::DOMAIN_NAME)
            );
        }
    }

}
