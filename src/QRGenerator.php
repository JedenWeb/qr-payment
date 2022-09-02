<?php declare(strict_types=1);

namespace JedenWeb\QRPayment;

class QRGenerator
{

	public function create(QRPayment $payment): string
	{
		return $this->createFromString($payment->toString());
	}

	public function createFromString(string $content): string
	{
		$builder = \Endroid\QrCode\Builder\Builder::create()
			->data($content)
			->writer(new \Endroid\QrCode\Writer\PngWriter())
			->size(300)
			->margin(10)
			->encoding(new \Endroid\QrCode\Encoding\Encoding('UTF-8'))
			->errorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium())
			->labelText('QR platba')
			->labelFont(new \Endroid\QrCode\Label\Font\OpenSans(12))
			->labelAlignment(new \Endroid\QrCode\Label\Alignment\LabelAlignmentLeft())
		;

		return $builder->build()->getString();
	}

}
