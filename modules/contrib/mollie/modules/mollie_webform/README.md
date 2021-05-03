# Mollie for Drupal Webform
Adds Webform as a payment context for Mollie for Drupal. This makes it possible to create payment forms (like donation
forms) on your website.

## Installation
Enable the module using Drush

`drush -y pm:enable mollie_webform`

or through the Drupal UI via the Manage > Extend menu option (_/admin/modules_).

### Dependencies
This module is a submodule of Mollie for Drupal and depends on that module. This module also depends on
[Webform](https://www.drupal.org/project/webform) version 6.0 or newer.

## Configuration

### Build a payment form
1. Build your webform according to the [Webform docs](https://www.drupal.org/docs/contributed-modules/webform).
2. Add an element of type Number to capture the amount for the payment.
3. (Optionally) add an element to capture the payment method to use in Mollie. This can be used to skip the payment
   method selection screen in Mollie. Options need to be supplied according to the
   [Mollie documentation](https://docs.mollie.com/reference/v2/methods-api/list-all-methods). There is currently no
   dedicated element that shows a list of available payment methods supplied by this module.
4. (Optionally) add an element to capture the description to use for the Mollie transaction. By default the module will
   use "[form title] #[submission ID]" as the description.
5. Add an element of type Mollie payment status. This element won't be visible on the website but shows the transaction
   status as captured from Mollie in the webform submissions.

### Configure the payment form
1. Add a Mollie payment handler via Settings > Emails / Handlers > Add handler on the webform entity.
2. Configure the currency, the element to capture the amount from and optionally the elements to capture the payment
   method and transaction description from.
   
## Usage
The module will update the Mollie payment status element in the webform submission every time that Mollie reports back
a status for the transaction corresponding to the webform submission. In this way the status of a transaction can be
seen in the webform submission.
