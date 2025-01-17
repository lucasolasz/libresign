<?php

namespace OCA\Libresign\Handler;

use OCA\Libresign\Exception\LibresignException;

class CfsslServerHandler {
	public const CSR_FILE = 'csr_server.json';
	public const CONFIG_FILE = 'config_server.json';

	public function createConfigServer(
		string $commonName,
		array $names,
		string $key,
		string $configPath
	): void {
		$this->putCsrServer(
			$commonName,
			$names,
			$configPath
		);
		$this->putConfigServer($key, $configPath);
	}

	private function putCsrServer(
		string $commonName,
		array $names,
		string $configPath
	): void {
		$filename = $configPath . self::CSR_FILE;
		$content = [
			'CN' => $commonName,
			'key' => [
				'algo' => 'rsa',
				'size' => 2048,
			],
		];
		foreach ($names as $name) {
			$content['names'][0][$name['id']] = $name['value'];
		}
		
		$response = file_put_contents($filename, json_encode($content));
		if ($response === false) {
			throw new LibresignException(
				"Error while writing CSR server file.\n" .
				"Remove the CFSSL API URI and Config path to use the default values.",
				500
			);
		}
	}

	private function putConfigServer(string $key, string $configPath): void {
		$filename = $configPath . self::CONFIG_FILE;
		$content = [
			'signing' => [
				'profiles' => [
					'CA' => [
						'auth_key' => 'key1',
						'expiry' => '8760h',
						'usages' => [
							"signing",
							"digital signature",
							"cert sign"
						],
					],
				],
			],
			'auth_keys' => [
				'key1' => [
					'key' => $key,
					'type' => 'standard',
				],
			],
		];

		$response = file_put_contents($filename, json_encode($content));
		if ($response === false) {
			throw new LibresignException("Error while writing config server file!", 500);
		}
	}
}
