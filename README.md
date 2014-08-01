This silex application shows a simple way to integrate Xero and BitPay to
allow customers to pay with Bitcoins.

# Requirements

* [Xero Account](https://www.xero.com/us/)
* [BitPay Account](https://bitpay.com/)
* [Vagrant Cloud Account](https://vagrantcloud.com/)

# Installation

```bash
git clone https://github.com/JoshuaEstes/xero-silex-app.git
cd xero-silex-app
composer install
vagrant up
vagrant share
```

# Configuration

If you have not already done so, make sure you have run `vagrant up` and
`vagrant share` as you will need the URL.

## Step 1

### Xero

* Log into your Xero Account, Goto `Settings > General Settings`
* Click on `Invoice Settings`
* Click on `Payment Services`
* Click on `Add Payment Service > Custom Payment URL`
* Put `Bitcoin` for name and `[VagrantShare URL]/xero/invoice?invoiceNo=[INVOICENUMBER]&currency=[CURRENCY]&amount=[AMOUNTDUE]&shortCode=[SHORTCODE]` for custom URL
* Once added, click the `Edit` link for Bitcoins and select `Invoice Themes`
* Select all Invoice Themes you want to allow users to pay with Bitcoins

### BitPay

* Log into your BitPay account and click on `API`
  * **NOTE** You can get a test account from https://test.bitpay.com
* Click on `API Access Keys`
* Click on `Add New API Key`
  * This key is used in `config/prod.php`

## Set 2

### Xero

* Head over to http://developer.xero.com and click on `My Applications`
* Click `Add Application`
* Select `Private` for your application. Enter the information required.
  * The certs that you generate should be put into the `config/certs` directory of this project.
  * Make note of the `Consumer Key` and `Consumer Secret`

## Set 3

Enter the keys that you acquired in `config/prod.php`

# Complete

If you have followed the Configuration steps, you are done and are ready to test
you app.
