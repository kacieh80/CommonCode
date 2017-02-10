<?php
require_once('Autoload.php');
require_once dirname(__FILE__) . '/../libs/PHPExcel/Classes/PHPExcel.php';
class ExcelTest extends PHPUnit_Framework_TestCase
{
    private function stringToExcel($data)
    {
         $name = tempnam("/tmp", "Excel");
         file_put_contents($name, $data);
         $excel = \PHPExcel_IOFactory::load($name);
         unlink($name);
         return $excel;
    }

    public function testBasic()
    {
        $serializer = new \Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $data = $serializer->serializeData('xls', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());

        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());

        $serializer = new \Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2'=>'a','ABC'=>'1'));
        $data = $serializer->serializeData('xlsx', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());

        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
    }

    public function testObject()
    {
        $serializer = new \Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $array = array($obj);
        $data = $serializer->serializeData('xls', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());

        $serializer = new \Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $array = array($obj);
        $data = $serializer->serializeData('xlsx', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());

        $serializer = new \Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $data = $serializer->serializeData('xls', $obj);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());

        $serializer = new \Serialize\ExcelSerializer();
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $data = $serializer->serializeData('xlsx', $obj);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
    }

    public function testComma()
    {
        $serializer = new \Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $data = $serializer->serializeData('xls', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2,3', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1,0', $cell->getValue());

        $serializer = new \Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $data = $serializer->serializeData('xlsx', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('Test2,3', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('ABC', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('a', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1,0', $cell->getValue());
    }

    public function testUnevenArrays()
    {
        $serializer = new \Serialize\ExcelSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $data = $serializer->serializeData('xls', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('A', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('B', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('C', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('3', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,3);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,3);
        $this->assertNotNull($cell);
        $this->assertEmpty($cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,3);
        $this->assertNotNull($cell);
        $this->assertEquals('2', $cell->getValue());

        $serializer = new \Serialize\ExcelSerializer();
        $row1 = array('A'=>1,'B'=>'2','C'=>'3');
        $row2 = array('A'=>1,'C'=>2);
        $array = array($row1, $row2);
        $data = $serializer->serializeData('xlsx', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('A', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('B', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('C', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('3', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,3);
        $this->assertNotNull($cell);
        $this->assertEquals('1', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,3);
        $this->assertNotNull($cell);
        $this->assertEmpty($cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,3);
        $this->assertNotNull($cell);
        $this->assertEquals('2', $cell->getValue());
    }

    public function testObjectContents()
    {
        //May need the Mongo Polyfill
        $tmp = new \Data\MongoDataSet(false);

        $serializer = new \Serialize\ExcelSerializer();
        $id = new \MongoId('4af9f23d8ead0e1d32000000');
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $row1 = array('A'=>$obj,'B'=>'2','C'=>'3','_id'=>$id);
        $array = array($row1);
        $data = $serializer->serializeData('xls', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('A', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('B', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('C', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(3,1);
        $this->assertNotNull($cell);
        $this->assertEquals('_id', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals("{\"Test1\":1,\"Test2\":\"a\",\"ABC\":\"1\"}", $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('3', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(3,2);
        $this->assertNotNull($cell);
        $this->assertEquals('4af9f23d8ead0e1d32000000', $cell->getValue());

        $serializer = new \Serialize\ExcelSerializer();
        $id = new \MongoId('4af9f23d8ead0e1d32000000');
        $obj = new stdClass();
        $obj->Test1 = 1;
        $obj->Test2 = 'a';
        $obj->ABC = '1';
        $row1 = array('A'=>$obj,'B'=>'2','C'=>'3','_id'=>$id);
        $array = array($row1);
        $data = $serializer->serializeData('xlsx', $array);
        $excel = $this->stringToExcel($data);
        $this->assertNotNull($excel);
        $sheet = $excel->getSheet();
        $this->assertNotNull($sheet);
        $cell = $sheet->getCellByColumnAndRow(0,1);
        $this->assertNotNull($cell);
        $this->assertEquals('A', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,1);
        $this->assertNotNull($cell);
        $this->assertEquals('B', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,1);
        $this->assertNotNull($cell);
        $this->assertEquals('C', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(3,1);
        $this->assertNotNull($cell);
        $this->assertEquals('_id', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(0,2);
        $this->assertNotNull($cell);
        $this->assertEquals("{\"Test1\":1,\"Test2\":\"a\",\"ABC\":\"1\"}", $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(1,2);
        $this->assertNotNull($cell);
        $this->assertEquals('2', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(2,2);
        $this->assertNotNull($cell);
        $this->assertEquals('3', $cell->getValue());
        $cell = $sheet->getCellByColumnAndRow(3,2);
        $this->assertNotNull($cell);
        $this->assertEquals('4af9f23d8ead0e1d32000000', $cell->getValue());
    }

    public function testBadType()
    {
        $serializer = new \Serialize\ExcelSerializer();
        $array = array(array('Test1'=>1,'Test2,3'=>'a','ABC'=>'1,0'));
        $data = $serializer->serializeData('text/json', $array);
        $this->assertNull($data);
    }

    public function testEmpty()
    {
        $serializer = new \Serialize\ExcelSerializer();
        $array = array();
        $data = $serializer->serializeData('xls', $array);
        $this->assertNull($data);
    }
}