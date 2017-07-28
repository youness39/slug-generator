<?php

/*
 * This file is part of the ausi/slug-generator package.
 *
 * (c) Martin Auswöger <martin@auswoeger.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ausi\SlugGenerator\Tests;

use Ausi\SlugGenerator\SlugGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class SlugGeneratorTest extends TestCase
{
	public function testInstantiation()
	{
		$this->assertInstanceOf(SlugGenerator::class, new SlugGenerator);
		$this->assertInstanceOf(SlugGenerator::class, new SlugGenerator([]));
	}

	/**
	 * @dataProvider getGenerate
	 *
	 * @param string $source
	 * @param string $expected
	 * @param array  $options
	 */
	public function testGenerate(string $source, string $expected, array $options = [])
	{
		$generator = new SlugGenerator($options);
		$this->assertSame($expected, $generator->generate($source));

		$generator = new SlugGenerator;
		$this->assertSame($expected, $generator->generate($source, $options));
	}

	/**
	 * @return array
	 */
	public function getGenerate(): array
	{
		return [
			['föobär', 'foobar'],
			[
				'föobär',
				'foeobaer',
				['locale' => 'de'],
			],
			[
				'föobär',
				'foobar',
				['locale' => 'en_US'],
			],
			[
				'Ö Äpfel-Fuß',
				'OE-Aepfel-Fuss',
				//'OE-AEpfel-Fuss',
				['valid' => 'a-zA-Z', 'locale' => 'de'],
			],
			[
				'Ö Ä Ü ẞ ÖX ÄX ÜX ẞX Öx Äx Üx ẞx Öö Ää Üü ẞß',
				'OE-AE-UE-SS-OEX-AEX-UEX-SSX-Oex-Aex-Uex-SSx-Oeoe-Aeae-Ueue-SSss',
				//'OE-AE-UE-SS-OEX-AEX-UEX-SSX-OEx-AEx-UEx-SSx-Oeoe-Aeae-Ueue-SSss',
				['valid' => 'a-zA-Z', 'locale' => 'de'],
			],
			[
				"O\u{308} A\u{308} U\u{308} O\u{308}X A\u{308}X U\u{308}X O\u{308}x A\u{308}x U\u{308}x O\u{308}o\u{308} A\u{308}a\u{308} U\u{308}u\u{308}",
				'OE-AE-UE-OEX-AEX-UEX-Oex-Aex-Uex-Oeoe-Aeae-Ueue',
				//'OE-AE-UE-OEX-AEX-UEX-OEx-AEx-UEx-Oeoe-Aeae-Ueue',
				['valid' => 'a-zA-Z', 'locale' => 'de'],
			],
			[
				'Ö Äpfel-Fuß',
				'ö-äpfel-fuß',
				['valid' => 'a-zäöüß'],
			],
			[
				'ö-äpfel-fuß',
				'OE__AEPFEL__FUSS',
				['valid' => 'A-Z', 'delimiter' => '__', 'locale' => 'de'],
			],
			['İNATÇI', 'inatci'],
			[
				'inatçı',
				'INATCI',
				['valid' => 'A-Z'],
			],
			[
				'İNATÇI',
				'inatçı',
				[
					'valid' => 'a-pr-vyzçğıöşü', // Turkish alphabet
					'locale' => 'tr',
				],
			],
			[
				'inatçı',
				'İNATÇI',
				[
					'valid' => 'A-PR-VYZÇĞİÖŞÜ', // Turkish alphabet
					'locale' => 'tr',
				],
			],
			['Καλημέρα', 'kalemera'],
			[
				'Καλημέρα',
				'kalimera',
				['locale' => 'el'],
			],
			['國語', 'guo-yu'],
			['김, 국삼', 'gim-gugsam'],
			[
				'富士山',
				'fu-shi-shan',
				['locale' => 'ja'],
			],
			[
				'富士山',
				'fù-shì-shān',
				['valid' => '\p{Latin}'],
			],
			[
				'Exämle <!-- % {{BR}} --> <a href="http://example.com">',
				'exämle-br-a-href-http-example-com',
				['valid' => '\p{Ll}'],
			],
			[
				'Exämle <!-- % {{BR}} --> <a href="http://example.com">',
				'EXÄMLE-BR-A-HREF-HTTP-EXAMPLE-COM',
				['valid' => '\p{Lu}'],
			],
			[
				'ǈ ǋ ǲ',
				'lj-nj-dz',
				['valid' => '\p{Ll}'],
			],
			[
				'ǈ ǋ ǲ',
				'LJ-NJ-DZ',
				['valid' => '\p{Lu}'],
			],
			[
				'ABC',
				'ac',
				['ignore' => 'b'],
			],
			[
				'Don’t they\'re',
				'dont-theyre',
				['ignore' => '’\''],
			],
			['фильм', 'film'],
			['Україна', 'ukraina'],
			['０ １ ９ ⑽ ⒒ ¼ Ⅻ', '0-1-9-10-11-1-4-xii'],
			['Č Ć Ž Š Đ č ć ž š đ', 'c-c-z-s-d-c-c-z-s-d'],
			['Ą Č Ę Ė Į Š Ų Ū Ž ą č ę ė į š ų ū ž', 'a-c-e-e-i-s-u-u-z-a-c-e-e-i-s-u-u-z'],
			[
				'abc',
				'1b3',
				[
					'valid' => 'b\d',
					'transforms' => ['a > 1; b > 1; c > 3;'],
				],
			],
			[
				'o ö',
				'o-x',
				['preTransforms' => ['ö > ä', 'ä > x']],
			],
			[
				'o ö',
				'o-o',
				['postTransforms' => ['ö > ä', 'ä > x']],
			],
			[
				'Damn 💩!!',
				'damn-chocolate-ice-cream',
				['preTransforms' => ['💩 > Chocolate \u0020 Ice \u0020 Cream']],
			],
			[
				'-A B C-',
				'abc',
				['delimiter' => ''],
			],
		];
	}

	public function testGenerateThrowsExceptionForNonUtf8Text()
	{
		$generator = new SlugGenerator;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageRegExp('(utf-?8)i');

		$generator->generate("\x80");
	}

	public function testGenerateThrowsExceptionForInvalidRule()
	{
		$generator = new SlugGenerator;

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageRegExp('("invalid rule".*"de_AT")');

		$generator->generate('foö', [
			'transforms' => ['invalid rule'],
			'locale' => 'de_AT',
		]);
	}

	/**
	 * @dataProvider getPrivateApplyTransformRule
	 *
	 * @param array  $parameters
	 * @param string $expected
	 */
	public function testPrivateApplyTransformRule(array $parameters, string $expected)
	{
		$generator = new SlugGenerator;
		$reflection = new \ReflectionClass(get_class($generator));
		$method = $reflection->getMethod('applyTransformRule');
		$method->setAccessible(true);

		$this->assertSame($expected, $method->invokeArgs($generator, $parameters));
	}

	/**
	 * @return array
	 */
	public function getPrivateApplyTransformRule(): array
	{
		return [
			[
				['abc', 'Upper', '', '/b+/'],
				'aBc',
			],
			[
				['öbc', 'Upper', '', '/b+/'],
				'öBc',
			],
			[
				['💩bc', 'Upper', '', '/b+/'],
				'💩Bc',
			],
			[
				['iı', 'Upper', 'tr', '/.+/'],
				'İI',
			],
			[
				['iı', 'Upper', '', '/.+/'],
				'II',
			],
			[
				['İI', 'Lower', 'tr_Latn_AT', '/.+/'],
				'iı',
			],
			[
				['İI', 'Lower', '', '/.+/'],
				'i̇i',
			],
			[
				['öß', 'ASCII', '', '/.+/'],
				'oss',
			],
			[
				['öß', 'ASCII', 'de', '/.+/'],
				'oess',
			],
			[
				['µ', 'Latin', 'pnt', '/.+/'],
				'm',
			],
			[
				['µ', 'Latin', '', '/.+/'],
				'µ',
			],
		];
	}
}
