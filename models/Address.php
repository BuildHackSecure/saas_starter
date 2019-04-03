<?php namespace Model\Core;


class Address
{


    private $address;

    private function __construct( $data ){
        $this->address = $data;
    }

    public function getId(){
        return (int)$this->address["id"];
    }

    /**
     * @return string
     */
    public function getCompany(){
        return $this->address["company"];
    }

    /**
     * @return string
     */
    public function getLine1(){
        return $this->address["line1"];
    }

    /**
     * @return string
     */
    public function getLine2(){
        return $this->address["line2"];
    }

    /**
     * @return string
     */
    public function getLine3(){
        return $this->address["line3"];
    }

    /**
     * @return string
     */
    public function getTown(){
        return $this->address["town"];
    }

    /**
     * @return string
     */
    public function getCounty(){
        return $this->address["county"];
    }

    /**
     * @return string
     */
    public function getPostcode(){
        return $this->address["postcode"];
    }

    /**
     * @return int
     */
    public function getCreatedAt(){
        return (int)$this->address["created_at"];
    }

    /**
     * @return string
     */
    public function getCountry(){
        return $this->address["country"];
    }

    public function update( $company, $line1, $line2, $line3, $town, $county, $postcode, $country ){
        $d = Db::write()->prepare('update address SET company=?,line1=?,line2=?,line3=?,town=?,county=?,postcode=?,country=? where id = ? ' );
        $d->execute( array($company, $line1, $line2, $line3, $town, $county, $postcode, $country, $this->getId() ) );
    }

    /**
     * @param $id
     * @return bool|Address
     */
    public static function get( $id ){
        $resp = false;
        $d = Db::read()->prepare('select * from address where id = ? LIMIT 1 ');
        $d->execute( array($id) );
        if( $d->rowCount() == 1 ){
            $resp = new Address( $d->fetch() );
        }
        return $resp;
    }

    /**
     * @param Account $account
     * @param $type
     * @return Address[]
     */
    public static function getAllByType( Account $account, $type  ){
        $resp = array();
        $d = Db::read()->prepare('select * from address where account_id = ? and type = ? order by id DESC ');
        $d->execute( array($account->getId(),$type) );
        while( $a = $d->fetch() ){
            $resp[] = new Address( $a );
        }
        return $resp;
    }

    /**
     * @param Account $account
     * @param $type
     * @return bool|Address
     */
    public static function getLatestByType( Account $account, $type  ){
        $resp = false;
        $d = Db::read()->prepare('select * from address where account_id = ? and type = ? order by id DESC LIMIT 1 ');
        $d->execute( array($account->getId(),$type) );
        if( $d->rowCount() == 1 ){
            $resp = new Address( $d->fetch() );
        }
        return $resp;
    }

    /**
     * @param Account $account
     * @param $company
     * @param $type
     * @param $line1
     * @param $line2
     * @param $line3
     * @param $town
     * @param $county
     * @param $postcode
     * @param $country
     * @return Address
     */
    public static function create(Account $account, $type, $company, $line1, $line2, $line3, $town, $county, $postcode, $country ){
        $d = Db::write()->prepare('insert into address (account_id,type,company, line1, line2, line3, town, county, postcode, country,created_at) values (?,?,?,?,?,?,?,?,?,?,?) ');
        $d->execute( array( $account->getId(), $type, $company, $line1, $line2, $line3, $town, $county, $postcode, $country, date("U") ) );
        return new Address( array(
            'id'            =>  Db::write()->lastInsertId(),
            'account_id'    =>  $account->getId(),
            'type'          =>  $type,
            'company'       =>  $company,
            'line1'         =>  $line1,
            'line2'         =>  $line2,
            'line3'         =>  $line3,
            'town'          =>  $town,
            'county'        =>  $county,
            'postcode'      =>  $postcode,
            'country'       =>  $country,
            'created_at'    =>  date("U")
        ));
    }


}