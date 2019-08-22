<?php declare(strict_types=1);

namespace JedenWeb\QRPayment;

use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;

/**
 * @author Pavel Jurásek
 */
class QRGenerator
{

	public function create(QRPayment $payment): string
	{
		return $this->createFromString($payment->toString());
	}

	public function createFromString(string $content): string
	{
		$code = new QrCode($content);
		$code->setSize(300);

		$code->setWriterByName('png');
		$code->setMargin(10);
		$code->setEncoding('UTF-8');
		$code->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM));
		$code->setLabel('QR platba', 12, null, LabelAlignment::LEFT);

		return $code->writeString();
	}

}
