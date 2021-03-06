<?php
require_once 'PHPUnit/Framework.php';

class TestingIPv6Address extends IPv6Address
{
	public static function factory($address)
	{
		return new TestingIPv6Address($address);
	}
	
	public function callBitwiseOperation($flag, IPAddress $other = NULL)
	{
		$this->bitwiseOperation($flag, $other);
	}
}

/**
 * Tests for the IPv6Address Class
 *
 * @package default
 * @author Marcus Cobden
 */
class IPv6AddressTest extends PHPUnit_Framework_TestCase
{

	public function providerFactory()
	{
		return array(
			array(
				'::1',
				'0000:0000:0000:0000:0000:0000:0000:0001'),
			array(
				'fe80::226:bbff:fe14:7372',
				'fe80:0000:0000:0000:0226:bbff:fe14:7372'),
			array(
				'::ffff:127:0:0:1',
				'0000:0000:0000:ffff:0127:0000:0000:0001'),
		);
	}

	/**
	 * @dataProvider providerFactory
	 */
	public function testFactory($input, $output)
	{
		$instance = IPv6Address::factory($input);
		
		$this->assertNotNull($instance);
		$this->assertEquals($output, (string) $instance);
	}
	
	public function providerFactoryException()
	{
		return array(
			array('256.0.0.1'),
			array('127.-1.0.1'),
			array('127.128.256.1'),
			array(12345),
			array(-12345),
			array('cake'),
			array('12345'),
			array('-12345'),
			array('0000:0000:0000:ffff:0127:0000:0000:000g'),
			array('000000000000ffff0127000000000001')
		);
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @dataProvider providerFactoryException
	 */
	public function testFactoryException($input)
	{
		IPv6Address::factory($input);
	}
	
	public function providerAddSubtract()
	{
		$data = array(
			array('::', '::', '::' ),
			array('::1', '::', '::1' ),
			array('::1', '::1', '::2' ),
			array('::1', '::2', '::3' ),
			array('::5', '::6', '::b' ),
			array('::10', '::1', '::11' ),
		);
		
		for ($i=0; $i < count($data); $i++)
			for ($j=0; $j < count($data[$i]); $j++)
				$data[$i][$j] = IPv6Address::factory($data[$i][$j]);
		
		return $data;
	}
	
	/**
	 * @dataProvider providerAddSubtract
	 */
	public function testAddSubtract($left, $right, $expected)
	{
		$result = $left->add($right);
		$this->assertEquals(0, $result->compareTo($expected));
		$again = $result->subtract($right);
		$this->assertEquals(0, $again->compareTo($left));
	}
	
	public function providerCompareTo()
	{
		$data = array(
			array('::', '::', 0),
			array('::1', '::1', 0),
			array('::1', '::2', -1),
			array('::2', '::1', 1),
			array('::f', '::1', 1),
			array('::a', '::b', -1),
		);
		
		for ($i=0; $i < count($data); $i++){
			$data[$i][0] = IPv6Address::factory($data[$i][0]);
			$data[$i][1] = IPv6Address::factory($data[$i][1]);
		}
		return $data;
	}
	
	/**
	 * @dataProvider providerCompareTo
	 */
	public function testCompareTo($left, $right, $expected)
	{
		$this->assertEquals($expected, $left->compareTo($right));
	}
	
	public function providerBitwise()
	{
		$data = array(
			//     OP1    OP2    AND    OR     XOR     NOT
			array('::1', '::1', '::1', '::1', '::0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'),
			array('::' , '::1', '::0', '::1', '::1', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
			array('::1', '::' , '::0', '::1', '::1', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:fffe'),
			array('::' , '::' , '::0', '::0', '::0', 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff'),
		);
		
		for ($i=0; $i < count($data); $i++) { 
			for ($j=0; $j < 6; $j++) { 
				$data[$i][$j] = IPv6Address::factory($data[$i][$j]);
			}
		}
		
		return $data;
	}
	
	/**
	 * @dataProvider providerBitwise
	 */
	public function testBitwise($ip1, $ip2, $ex_and, $ex_or, $ex_xor, $ex_not)
	{		
		$this->assertEquals((string) $ex_and, (string) $ip1->bitwiseAND($ip2));
		$this->assertEquals((string) $ex_or , (string) $ip1->bitwiseOR($ip2));
		$this->assertEquals((string) $ex_xor, (string) $ip1->bitwiseXOR($ip2));
		$this->assertEquals((string) $ex_not, (string) $ip1->bitwiseNOT());
	}
	
	public function testBitwiseException()
	{
		
		$ip = TestingIPv6Address::factory('::1');
		
		try
		{
			$ip->callBitwiseOperation('!', $ip);
			$this->fail('An expected exception has not been raised.');
		}
		catch (InvalidArgumentException $e){}
		
		$ip->callBitwiseOperation('&', $ip);
		$ip->callBitwiseOperation('|', $ip);
		$ip->callBitwiseOperation('^', $ip);
		$ip->callBitwiseOperation('~');
	}
	
	// 
	// public function providerAsIPv4Address()
	// {
	// 	return array(
	// 		array('0000:0000:0000:ffff:0127:0000:0000:0001', '127.0.0.1'),
	// 	);
	// }
	// 
	// /**
	//  * @dataProvider providerAsIPv4Address
	//  */
	// public function testAsIPv4Address($v6, $v4 = NULL)
	// {
	// 	$ip = new IPv6Address($v6);
	// 	
	// 	if ($v4 === NULL)
	// 		$this->assertFalse($ip->isEncodedIPv4Address());
	// 	else
	// 		$this->assertEquals($v4, (string) $ip->asIPv4Address());
	// 	
	// }
}
