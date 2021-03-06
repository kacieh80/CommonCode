<?php
require_once('Autoload.php');
class DataSetTest extends PHPUnit\Framework\TestCase
{
    public function testDataSet()
    {
        $dataSet = new \Data\DataSet();
        $dataSet->offsetSet(0, 'test');
        $this->assertTrue(true);
        $dataSet->offsetSet('a', 'test');
        $this->assertTrue(true);
        $dataSet[0] = 'test';
        $this->assertTrue(true);
        $dataSet['a'] = 'test';
        $this->assertTrue(true);
        try
        {
            isset($dataSet['a']);
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        try
        {
            isset($dataSet[0]);
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        unset($dataSet['a']);
        unset($dataSet[0]);
        try
        {
            $data = $dataSet['a'];
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        try
        {
            $data = $dataSet[0];
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
        try
        {
            $dataSet->raw_query('SELECT * from tblUsers WHERE 1');
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }
    }

    public function testSQLDataSet()
    {
        $dataSet = new \Data\SQLDataSet(array('dsn'=>'sqlite::memory:'));
        $this->assertInstanceOf('Data\SQLDataSet', $dataSet);
        $res = $dataSet->raw_query('CREATE TABLE Persons (PersonID int, LastName varchar(255), FirstName varchar(255), Address varchar(255), City varchar(255));');
        $this->assertNotFalse($res);

        $res = $dataSet->raw_query('CREATE TABLE tblUsers (UID int, UserName varchar(255), Password varchar(255), PersonID int);');
        $this->assertNotFalse($res);

        $res = $dataSet->raw_query('CREATE VIEW vUserPeople AS SELECT tblUsers.UID, tblUsers.UserName, Persons.LastName, Persons.FirstName FROM Persons INNER JOIN tblUsers ON Persons.PersonID = tblUsers.PersonID;');
        $this->assertNotFalse($res);

        $res = $dataSet->raw_query('Test=A');
        $this->assertFalse($res);

        $this->assertTrue(isset($dataSet['Persons']));
        $this->assertTrue(isset($dataSet['Users']));
        $this->assertTrue(isset($dataSet['UserPeople']));
        $this->assertFalse(isset($dataSet['Test']));

        try
        {
            $dataSet['Test'];
            $this->assertTrue(false);
        }
        catch(\Exception $ex)
        {
            $this->assertTrue(true);
        }

        $dataTable = $dataSet['Users'];
        $this->assertNotFalse($dataTable);
        $this->assertInstanceOf('Data\SQLDataTable', $dataTable);

        $dataTable = $dataSet['UserPeople'];
        $this->assertNotFalse($dataTable);
        $this->assertInstanceOf('Data\SQLDataTable', $dataTable);

        $dataTable = $dataSet['Persons'];
        $this->assertNotFalse($dataTable);
        $this->assertInstanceOf('Data\SQLDataTable', $dataTable);

        $res = $dataTable->create(array('PersonID'=>1, 'LastName'=>'Test', 'FirstName'=>'Bob', 'Address'=>'123 Fake Street', 'City'=>'Fake Town'));
        $this->assertTrue($res);

        $res = $dataTable->create(array('PersonID'=>1, 'LastName'=>'Test', 'FirstName'=>'Bob', 'Address'=>'123 Fake Street', 'City'=>'Fake Town', 'State'=>'TX'));
        $this->assertFalse($res);

        $obj = new stdClass();
        $obj->PersonID = 3;
        $obj->LastName = 'Boyd';
        $obj->FirstName = 'Patrick';
        $obj->Address ='321 Fake Street';
        $obj->City = 'Temp';
        $res = $dataTable->create($obj);
        $this->assertTrue($res);

        $res = $dataTable->create(array('PersonID'=>2, 'LastName'=>'Abc', 'FirstName'=>'123', 'Address'=>'123 Fake Street', 'City'=>'Fake Town'));
        $this->assertTrue($res);

        $res = $dataTable->read(false);
        $this->assertCount(3, $res);

        $res = $dataTable->read(new \Data\Filter('PersonID eq 1'));
        $this->assertCount(1, $res);
        $this->assertCount(5, $res[0]);
        $this->assertEquals(1, $res[0]['PersonID']);
        $this->assertEquals('Test', $res[0]['LastName']);

        $res = $dataTable->read(new \Data\Filter('PersonID eq 2'), array('PersonID', 'Address'));
        $this->assertCount(1, $res);
        $this->assertCount(2, $res[0]);
        $this->assertEquals(2, $res[0]['PersonID']);
        $this->assertEquals('123 Fake Street', $res[0]['Address']);

        $res = $dataTable->read(new \Data\Filter('PersonID eq 4'));
        $this->assertFalse($res);

        $res = $dataTable->read(false, false, 1);
        $this->assertCount(1, $res);

        $res = $dataTable->read(false, false, 1, 1);
        $this->assertCount(1, $res);

        $res = $dataTable->read(false, false, false, false, array('PersonID'=>'DESC'));
        $this->assertCount(3, $res);
        $this->assertCount(5, $res[0]);
        $this->assertEquals(3, $res[0]['PersonID']);

        $res = $dataTable->update(new \Data\Filter('PersonID eq 1'), array('LastName'=>'Smith'));
        $this->assertTrue($res);

        $obj = new stdClass();
        $obj->FirstName = 'Tim';
        $res = $dataTable->update(new \Data\Filter('PersonID eq 2'), $obj);
        $this->assertTrue($res);

        $res = $dataTable->update(new \Data\Filter('PersonID eq 3'), array('LastName1'=>'Smith'));
        $this->assertFalse($res);

        $res = $dataTable->read(new \Data\Filter('PersonID eq 1'));
        $this->assertCount(1, $res);
        $this->assertEquals('Smith', $res[0]['LastName']);

        $res = $dataTable->delete(new \Data\Filter('PersonID eq 2'));
        $this->assertTrue($res);

        $res = $dataTable->read(false);
        $this->assertCount(2, $res);

        $res = $dataTable->delete(new \Data\Filter('Test eq 4'));
        $this->assertFalse($res);
    }

    public function testUnknownDataSet()
    {
        try
        {
             \DataSetFactory::getDataSetByName('Unknown');
             $this->assertFalse(true);
        }
        catch(\Exception $ex)
        {
            $this->assertEquals('Unknown dataset name Unknown', $ex->getMessage());
        }
    }
}
/* vim: set tabstop=4 shiftwidth=4 expandtab: */
