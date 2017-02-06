<?php


class Netzarbeiter_NicerImageNames_Test_Helper_ImageTest
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var string
     */
    protected $_class = 'Netzarbeiter_NicerImageNames_Helper_Image';

    /**
     * @var Netzarbeiter_NicerImageNames_Helper_Image
     */
    protected $_instance;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockProduct;
    
    public function setUp()
    {
        /** @var Netzarbeiter_NicerImageNames_Helper_Image $instance */
        $this->_instance = new $this->_class;

        $this->_mockProduct = $this->getMock('Mage_Catalog_Model_Product', array('getName'));
        $method = new ReflectionMethod($this->_instance, 'setProduct');
        $method->setAccessible(true);
        $method->invoke($this->_instance, $this->_mockProduct);

        $mockImageModel = $this->getMock('Mage_Catalog_Model_Product_Image');
        $mockImageModel->expects($this->any())
            ->method('getDestinationSubdir')
            ->will($this->returnValue(sys_get_temp_dir()));

        $method = new ReflectionMethod($this->_instance, '_setModel');
        $method->setAccessible(true);
        $method->invoke($this->_instance, $mockImageModel);
        
        Mage::app()->getStore()->setConfig('catalog/nicerimagenames/unique', 0);
    }

    public function nameProvider()
    {
        // attribute value | for file | for label
        return array(
            array('ä', 'a', 'ä'),
            array('ö', 'o', 'ö'),
            array('ü', 'u', 'ü'),
            array('ß', 'ss', 'ß'),
            array('é', 'e', 'é'),
            array('è', 'e', 'è'),
            array('ô', 'o', 'ô'),
            array('ç', 'c', 'ç'),
            array('ã', 'a', 'ã'),
            array('õ', 'o', 'õ'),
            array('æ', 'ae', 'æ'),
            array('à', 'a', 'à'),
            array('a a', 'a-a', 'a a'),
            array('a  a', 'a-a', 'a a'), // double space
            array('a_a', 'a-a', 'a_a'),
            array('a__a', 'a-a', 'a__a'), // double underscore
            array('a-a', 'a-a', 'a-a'),
            array('a--a', 'a-a', 'a-a'), // double minus
            array('%', '', '%'),
            array('a#a', 'a-a', 'a#a'),
            array('a/a', 'a-a', 'a/a'),
            array('a:a', 'a-a', 'a:a'),
            array('a..a', 'a_a', 'a..a'),
            array('&', 'and', 'and'),
            array('a"a', 'a-a', 'a&quot;a'),
            array("a'a", 'a-a', 'aa'),
            array("a<a", 'a-a', 'a&lt;a'),
            array("a>a", 'a-a', 'a&gt;a'),
            array("-a-", 'a', '-a-'),
            array("_a_", 'a', '_a_'),
            array("_-a-_", 'a', '_-a-_'),
        );
    }

    /**
     * @dataProvider nameProvider
     */
    public function testGetGeneratedNameForImageAttributeForFiles($name, $expected)
    {
        $this->_mockProduct->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue($name));
        
        $method = new ReflectionMethod($this->_class, '_getGeneratedNameForImageAttribute');
        $method->setAccessible(true);
        
        // for files
        $result = $method->invoke($this->_instance, 'image', '%name', true);
        $this->assertEquals($expected, $result, "Unexpected result for '$name''");
    }

    /**
     * @dataProvider nameProvider
     */
    public function testGetGeneratedNameForImageAttributeForLabels($name, $dummy, $expected)
    {
        $this->_mockProduct->expects($this->once())
            ->method('getName')
            ->with()
            ->will($this->returnValue($name));
        
        $method = new ReflectionMethod($this->_class, '_getGeneratedNameForImageAttribute');
        $method->setAccessible(true);
        
        // for label
        $result = $method->invoke($this->_instance, 'image', '%name', false);
        $this->assertEquals($expected, $result, "Unexpected result for '$name''");
    }

    /**
     * @param string $map
     * @param array $expected
     * @dataProvider mapProvider
     */
    public function testItShouldParsePlaceholders($map, array $expected)
    {
        $result = $this->_instance->getAttributePlaceholdersFromMap($map);
        $this->assertEquals($expected, $result);
    }
    
    public function mapProvider()
    {
        return array(
            array('foo %foo', array(array('%foo', 'foo'))),
            array('foo %fooBar', array(array('%fooBar', 'foo_bar'))),
            array('foo %fooBarBaz', array(array('%fooBarBaz', 'foo_bar_baz'))),
            array('foo %foo-Bar', array(array('%foo', 'foo'))),
            array('foo %foo1-Bar', array(array('%foo1', 'foo1', ))),
            array('foo %foo %bar', array(
                array('%foo', 'foo'),
                array('%bar', 'bar')
            )),
            array('foo %fooBar %barBaz', array(
                array('%fooBar', 'foo_bar'),
                array('%barBaz', 'bar_baz')
            )),
            array('foo %{foo}', array(array('%{foo}', 'foo'))),
            array('foo %{fooBar}', array(array('%{fooBar}', 'foo_bar'))),
            array('foo %{fooBarBaz}', array(array('%{fooBarBaz}', 'foo_bar_baz'))),
            array('foo %{foo}-Bar', array(array('%{foo}', 'foo'))),
            array('foo %{foo1}-Bar', array(array('%{foo1}', 'foo1', ))),
            array('foo %{foo} %bar', array(
                array('%{foo}', 'foo'),
                array('%bar', 'bar')
            )),
            array('foo %{fooBar} %{barBaz}', array(
                array('%{fooBar}', 'foo_bar'),
                array('%{barBaz}', 'bar_baz')
            )),
            
            array('foo %{foo} %{fooBar} %{fooBaz} %{foobar}', array(
                array('%{foo}', 'foo'),
                array('%{fooBar}', 'foo_bar'),
                array('%{fooBaz}', 'foo_baz'),
                array('%{foobar}', 'foobar'),
            )),
        );
    }
} 