<?php declare(strict_types=1);

namespace JedenWeb\QRPayment;

/**
 * @author Pavel Jurásek
 */
class QRPayment
{

	/** @var array */
	private $fields = [];

	/** @var string */
	private $checksum;

	public function __construct(string $iban)
	{
		if (!verify_iban($iban)) {
			throw new InvalidFormat(sprintf('IBAN %s is not valid.', $iban));
		}

		$this->fields['ACC'] = $iban;
		$this->calculateChecksum();
	}

	public function setAmount(string $amount): self
	{
		if (!is_numeric($amount)) {
			throw new DisallowedCharacter('Amount doesn\'t seem to be a number.');
		}

		$amount = number_format((float) $amount, 2, '.', '');

		if (strlen($amount) > 10) {
			throw new AllowedLengthExceeded('Amount can be at most 10 characters long.');
		}

		$this->fields['AM'] = $amount;
		$this->calculateChecksum();

		return $this;
	}

	public function setCurrency(string $currency): self
	{
		if (!preg_match('~^[A-Z]{3}\z~', $currency)) {
			throw new InvalidFormat(sprintf('Currency code %s is not valid.', $currency));
		}

		$this->fields['CC'] = $currency;
		$this->calculateChecksum();

		return $this;
	}

	public function setReceiverPaymentIdentificator(string $identificator): self
	{
		if (strlen($identificator) > 16) {
			throw new AllowedLengthExceeded('Receiver payment identificator can be at most 16 characters long.');
		}

		$this->fields['RF'] = $identificator;
		$this->calculateChecksum();

		return $this;
	}

	public function setReceiverName(string $name): self
	{
		if (strlen($name) > 35) {
			throw new AllowedLengthExceeded('Receiver name can be at most 35 characters long.');
		} elseif (!preg_match('~^[0-9A-Z \$%\*\+\-\.\/:]*\z~', $name)) {
			throw new DisallowedCharacter('Field can contain only these characters: 0-9, A-Z, (space), $, %, *, +, -, ., /, :');
		}

		$this->fields['RN'] = $name;
		$this->calculateChecksum();

		return $this;
	}

	public function setMaturityDate(\DateTime $date): self
	{
		$this->fields['DT'] = $date->format('Ymd');
		$this->calculateChecksum();

		return $this;
	}

	public function setPaymentType(string $type): self
	{
		if (strlen($type) > 3) {
			throw new AllowedLengthExceeded('Payment type can be at most 3 characters long.');
		} elseif (!preg_match('~^[0-9A-Z \$%\*\+\-\.\/:]*\z~', $type)) {
			throw new DisallowedCharacter('Field can contain only these characters: 0-9, A-Z, (space), $, %, *, +, -, ., /, :');
		}

		$this->fields['PT'] = $type;
		$this->calculateChecksum();

		return $this;
	}

	public function setMessage(string $message): self
	{
		if (strlen($message) > 60) {
			throw new AllowedLengthExceeded('Message can be at most 60 characters long.');
		} elseif (!preg_match('~^[0-9A-Z \$%\*\+\-\.\/\:]*\z~', $message)) {
			throw new DisallowedCharacter('Field can contain only these characters: 0-9, A-Z, (space), $, %, *, +, -, ., /, :');
		}

		$this->fields['MSG'] = $message;
		$this->calculateChecksum();

		return $this;
	}

	public function setNotification(string $channel, string $address): self
	{
		if ($channel !== 'P' && $channel !== 'E') {
			throw new DisallowedCharacter('Notification can be either P(hone) or E(-mail).');
		}

		if ($channel === 'P' && !preg_match('~^(\+|00)[0-9]{2,3}[0,9]{0,12}\z~', $address)) {
			throw new InvalidFormat('Phone number format is not valid.');
		}

		$this->fields['NT'] = $channel;
		$this->fields['NTA'] = $address;
		$this->calculateChecksum();

		return $this;
	}

	public function setRetry(int $days): self
	{
		if ($days < 0 || $days > 30) {
			throw new DisallowedCharacter('Number of retries is out of range. Min is 0, max is 30.');
		}

		$this->fields['X-PER'] = (string) $days;
		$this->calculateChecksum();

		return $this;
	}

	public function setVs(string $vs): self
	{
		$this->validateSymbol($vs, 'Variable');

		$this->fields['X-VS'] = $vs;
		$this->calculateChecksum();

		return $this;
	}

	public function setSk(string $ss): self
	{
		$this->validateSymbol($ss, 'Specific');

		$this->fields['X-SS'] = $ss;
		$this->calculateChecksum();

		return $this;
	}

	public function setKs(string $ks): self
	{
		$this->validateSymbol($ks, 'Constant');

		$this->fields['X-KS'] = $ks;
		$this->calculateChecksum();

		return $this;
	}

	public function setIssuerId(string $id): self
	{
		if (strlen($id) > 20) {
			throw new AllowedLengthExceeded('Issuer ID can be at most 20 characters long.');
		}

		$this->fields['X-ID'] = $id;
		$this->calculateChecksum();

		return $this;
	}

	public function setUrl(string $url): self
	{
		if (strlen($url) > 140) {
			throw new AllowedLengthExceeded('URL can be at most 140 characters long.');
		}

		$this->fields['X-URL'] = $url;
		$this->calculateChecksum();

		return $this;
	}

	private function validateSymbol(string $symbol, string $name): void
	{
		if (!is_numeric($symbol)) {
			throw new DisallowedCharacter(sprintf('%s symbol must be a number.', $name));
		} elseif (strlen($symbol) > 10) {
			throw new AllowedLengthExceeded(sprintf('%s symbol can be at most 10 characters long.', $name));
		}
	}

	private function calculateChecksum(): void
	{
		if (isset($this->fields['CRC32'])) {
			unset($this->fields['CRC32']);
		}
		ksort($this->fields);

//		var_dump($this->convertToString($this->fields));
		$this->checksum = dechex(crc32($this->convertToString($this->fields)));
		$this->fields['CRC32'] = $this->checksum;
	}

	public function getChecksum(): string
	{
		if ($this->checksum === null) {
			$this->calculateChecksum();
		}

		return $this->checksum;
	}

	private function convertToString(array $fields): string
	{
		$str = 'SPD*1.0';
		foreach ($fields as $key => $value) {
			$str .= sprintf('*%s:%s', $key, $value);
		}

		return $str;
	}

	public function toString(): string
	{
		return $this->convertToString($this->fields);
	}

	public function __toString(): string
	{
		return $this->convertToString($this->fields);
	}

}
