<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com and you will be sent a copy immediately.
 *
 * Created on 02.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */

/**
 * Iways PayPalPlus Helper
 *
 * @category   Iways
 * @package    Iways_PayPalPlus
 * @author robert
 */
class Iways_PayPalPlus_Helper_Test extends Mage_Payment_Helper_Data
{
    public function getPaymentSaleCompleted()
    {
        return new \PayPal\Api\WebhookEvent('{
	"id": "WH-2WR32451HC0233532-67976317FL4543714",
	"create_time": "2014-10-23T17:23:52Z",
	"resource_type": "sale",
	"event_type": "PAYMENT.SALE.COMPLETED",
	"summary": "A successful sale payment was made for 130,95 Euro",
	"resource": {
		"amount": {
			"total": "130.95",
			"currency": "EUR"
		},
		"id": "80021663DE681814L",
		"parent_payment": "PAY-74T71634YM527604VKW3W3YI",
		"update_time": "2014-10-23T17:23:04Z",
		"clearing_time": "2014-10-30T07:00:00Z",
		"state": "completed",
		"payment_mode": "ECHECK",
		"create_time": "2014-10-23T17:22:56Z",
		"links": [
			{
				"href": "https://api.paypal.com/v1/payments/sale/80021663DE681814L",
				"rel": "self",
				"method": "GET"
			},
			{
				"href": "https://api.paypal.com/v1/payments/sale/80021663DE681814L/refund",
				"rel": "refund",
				"method": "POST"
			},
			{
				"href": "https://api.paypal.com/v1/payments/payment/PAY-1PA12106FU478450MKRETS4A",
				"rel": "parent_payment",
				"method": "GET"
			}
		],
		"protection_eligibility_type": "ITEM_NOT_RECEIVED_ELIGIBLE,UNAUTHORIZED_PAYMENT_ELIGIBLE",
		"protection_eligibility": "ELIGIBLE"
	},
	"links": [
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-2WR32451HC0233532-67976317FL4543714",
			"rel": "self",
			"method": "GET"
		},
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-2WR32451HC0233532-67976317FL4543714/resend",
			"rel": "resend",
			"method": "POST"
		}
	]
}');
    }

    public function getPaymentSalePending()
    {
        return new \PayPal\Api\WebhookEvent('{
	"id": "WH-6W4482673W002281V-61985753LP2332451",
	"create_time": "2015-05-11T21:45:15Z",
	"resource_type": "sale",
	"event_type": "PAYMENT.SALE.PENDING",
	"summary": "Payment pending for EUR 3.76 EUR",
	"resource": {
		"reason_code": "RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION",
		"amount": {
			"total": "3.76",
			"currency": "EUR"
		},
		"id": "11139561TK568332L",
		"parent_payment": "PAY-74T71634YM527604VKW3W3YI",
		"update_time": "2015-05-11T21:44:29Z",
		"state": "pending",
		"payment_mode": "INSTANT_TRANSFER",
		"create_time": "2015-05-11T21:44:29Z",
		"links": [
			{
				"href": "https://api.paypal.com/v1/payments/sale/11139561TK568332L",
				"rel": "self",
				"method": "GET"
			},
			{
				"href": "https://api.paypal.com/v1/payments/sale/11139561TK568332L/refund",
				"rel": "refund",
				"method": "POST"
			},
			{
				"href": "https://api.paypal.com/v1/payments/payment/PAY-13V79659LS5126423KVISFPI",
				"rel": "parent_payment",
				"method": "GET"
			}
		],
		"protection_eligibility": "INELIGIBLE"
	},
	"links": [
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-6W4482673W002281V-61985753LP2332451",
			"rel": "self",
			"method": "GET"
		},
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-6W4482673W002281V-61985753LP2332451/resend",
			"rel": "resend",
			"method": "POST"
		}
	]
}');
    }

    public function getPaymentSaleRefunded()
    {
        return new \PayPal\Api\WebhookEvent('{
	"id": "WH-2N242548W9943490U-1JU23391CS4765624",
	"create_time": "2014-10-31T15:42:24Z",
	"resource_type": "sale",
	"event_type": "PAYMENT.SALE.REFUNDED",
	"summary": "A 0.01 USD sale payment was refunded",
	"resource": {
		"amount": {
			"total": "-0.01",
			"currency": "USD"
		},
		"id": "6YX43824R4443062K",
		"parent_payment": "PAY-74T71634YM527604VKW3W3YI",
		"update_time": "2014-10-31T15:41:51Z",
		"state": "completed",
		"create_time": "2014-10-31T15:41:51Z",
		"links": [
			{
				"href": "https://api.paypal.com/v1/payments/refund/6YX43824R4443062K",
				"rel": "self",
				"method": "GET"
			},
			{
				"href": "https://api.paypal.com/v1/payments/payment/PAY-5437236047802405NKRJ22UA",
				"rel": "parent_payment",
				"method": "GET"
			},
			{
				"href": "https://api.paypal.com/v1/payments/sale/9T0916710M1105906",
				"rel": "sale",
				"method": "GET"
			}
		],
		"sale_id": "9T0916710M1105906"
	},
	"links": [
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-2N242548W9943490U-1JU23391CS4765624",
			"rel": "self",
			"method": "GET"
		},
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-2N242548W9943490U-1JU23391CS4765624/resend",
			"rel": "resend",
			"method": "POST"
		}
	]
}');
    }

    public function getPaymentSaleReversed()
    {
        return new \PayPal\Api\WebhookEvent('{
	"id": "WH-3EC545679X386831C-3D038940937933201",
	"create_time": "2014-10-23T00:19:27Z",
	"resource_type": "sale",
	"event_type": "PAYMENT.SALE.REVERSED",
	"summary": "A $ 0.49 USD sale payment was reversed",
	"resource": {
		"amount": {
			"total": "-0.49",
			"currency": "USD",
			"details": {
				"subtotal": "-0.64",
				"tax": "0.08",
				"shipping": "0.07"
			}
		},
		"id": "80021663DE681814L",
		"state": "completed",
		"create_time": "2014-10-23T00:19:12Z",
		"links": [
			{
				"href": "https://api.paypal.com/v1/payments/refund/77689802DL785834G",
				"rel": "self",
				"method": "GET"
			}
		]
	},
	"links": [
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-3EC545679X386831C-3D038940937933201",
			"rel": "self",
			"method": "GET"
		},
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-3EC545679X386831C-3D038940937933201/resend",
			"rel": "resend",
			"method": "POST"
		}
	]
}');
    }

    public function getRiskDisputeCreated()
    {
        return new \PayPal\Api\WebhookEvent('{
	"id": "WH-3RA31609V56026446-3AM32121NY391352E",
	"create_time": "2015-04-27T12:27:59Z",
	"resource_type": "dispute",
	"event_type": "RISK.DISPUTE.CREATED",
	"summary": "A new dispute opened with Case # PP-000-001-202-018",
	"resource": {
		"dispute_creation_date": 1430132049000,
		"dispute_amount": {
			"currency": "USD",
			"value": "1001"
		},
		"buyer_account_number": "1680371069474696023",
		"buyer_email": "gganesan-2578561059364249@paypal.com",
		"seller_payment_id": "93U280418G152174E",
		"dispute_category": 1,
		"dispute_reason": 2,
		"dispute_channel": 1,
		"seller_account_number": "1741545641006354220",
		"dispute_message_history": [
			{
				"message": "Tamil testing. Making INR to SNAD.",
				"actor": 1
			}
		],
		"buyer_payment_id": "51B54593E8586244A",
		"seller_email": "gganesan-2578631019666745@paypal.com",
		"dispute_status": 1000,
		"dispute_id": "PP-000-001-202-018"
	},
	"links": [
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-3RA31609V56026446-3AM32121NY391352E",
			"rel": "self",
			"method": "GET"
		},
		{
			"href": "https://api.paypal.com/v1/notifications/webhooks-events/WH-3RA31609V56026446-3AM32121NY391352E/resend",
			"rel": "resend",
			"method": "POST"
		}
	]
}');
    }
}