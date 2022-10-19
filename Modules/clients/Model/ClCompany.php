<?php

require_once 'Framework/Model.php';

class ClCompany extends Model
{
    public function __construct()
    {
        $this->tableName = "cl_company";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", 0);
        $this->setColumnsInfo("address", "text", "");
        $this->setColumnsInfo("zipcode", "varchar(255)", 0);
        $this->setColumnsInfo("city", "varchar(255)", 0);
        $this->setColumnsInfo("county", "varchar(255)", 0);
        $this->setColumnsInfo("country", "varchar(255)", 0);
        $this->setColumnsInfo("tel", "varchar(255)", 0);
        $this->setColumnsInfo("fax", "varchar(255)", 0);
        $this->setColumnsInfo("email", "varchar(255)", 0);
        $this->setColumnsInfo("approval_number", "varchar(255)", 0);

        $this->primaryKey = "id";
    }

    public function default($idSpace)
    {
        return [
            "id" => 0,
            "id_space" => $idSpace,
            "name" => "",
            "address" => "",
            "zipcode" => "",
            "city" => "",
            "county" => "",
            "country" => "",
            "tel" => "",
            "fax" => "",
            "email" => "",
            "approval_number" => ""
        ];
    }

    /**
     * Get the clCompany for this space
     * @param string|int $idSpace
     *
     * @return array
     */
    public function getForSpace($idSpace)
    {
        $sql = "SELECT * FROM cl_company WHERE id_space=? AND deleted=0";
        $clCompany = $this->runRequest($sql, array($idSpace))->fetch();
        if ($clCompany === false) {
            return [];
        }
        return $clCompany;
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM cl_company WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT reference FROM cl_company WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $idSpace))->fetch();
        return $data[0];
    }

    public function set(
        $idSpace,
        $name,
        $address,
        $zipcode,
        $city,
        $county,
        $country,
        $tel,
        $fax,
        $email,
        $approval_number
    )
    {
        $id = $this->exists($idSpace);
        if (!$id) {
            $sql = 'INSERT INTO cl_company (id_space, name, address, zipcode, city, 
            county, country, tel, fax, email, approval_number) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array( $idSpace, $name, $address, $zipcode, $city,
            $county, $country, $tel, $fax, $email, $approval_number ));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_company SET name=?, address=?, zipcode=?, 
                city=?, county=?, country=?, tel=?, fax=?, email=?, approval_number=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $address, $zipcode, $city,
            $county, $country, $tel, $fax, $email, $approval_number, $id, $idSpace));
            return $id;
        }
    }

    protected function exists($idSpace)
    {
        $sql = "SELECT id FROM cl_company WHERE id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($idSpace, $id)
    {
        $sql = "UPDATE cl_company SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
