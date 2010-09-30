<?php

    class Model
    {

        public $id = -1;
        
        public function save()
        {
			include("db.inc.php");
            
            $ref = new ReflectionClass($this);
            
            $name = $DB_PREFIX.$ref->getName();
            
            $props = array();
            
            $i = 0; 
           
            $props = array();
            $values = array();  
			$types = array();
             
                foreach($ref->getProperties() as $prop)
                {
                    if($prop->getName() != "id")
                    {
                        array_push($props, $prop->getName());
						array_push($types, gettype($prop->getValue($this)));
						if(gettype($prop->getValue($this)) == "string")
							array_push($values, "\"".$prop->getValue($this)."\"");
						else
							array_push($values, $prop->getValue($this));
						
                    }   
                }
            
           
			
			
				
            
            
            
            // Create table if needed
            if(mysql_num_rows( Model::dbQuery("SHOW TABLES LIKE '".$name."'"))<=0)
            {
                $varsarray = array();
				$dbtypes = array("string" => "varchar(255)", "integer" => "int");
					
				for($i = 0; $i < sizeof($props); $i++)
				{
					array_push($varsarray, $props[$i]." ".$dbtypes[$types[$i]]);	
				}
					
				$vars = implode(", ", $varsarray);
				
				$sql = "CREATE TABLE ".$name." ( "; 
                
      
                $sql .= "`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
                //$sql .= implode(" varchar(255), ", $props);
                //$sql .= " varchar(255) ";
				$sql .= $vars;
                $sql .= ")";

                echo $sql;
                
                if(!Model::dbQuery($sql))
                {
                    echo mysql_error();
                }
                
            }
            
            // If id exists remove
            
            $sql = "DELETE FROM ".$name." WHERE `id`=".$this->id." LIMIT 1"; 
            if(!Model::dbQuery($sql))
                {
                    echo mysql_error();
                }
                
                
            // Add
            if($this->id >= 0)
                $sql = "INSERT INTO ".$name."(id, ".implode(",", $props).") VALUES(".$this->id.", ".implode(",", $values).")";
            else
                $sql = "INSERT INTO ".$name."(".implode(",", $props).") VALUES(".implode(",", $values).")";
            
            if(!Model::dbQuery($sql))
            {
                echo mysql_error();
            }
           
        }
        
        public static function getAllObjects($order)
        {
			include("db.inc.php");
		
            $objects = array();
            $name =  $DB_PREFIX.get_called_class();
			
            if($order != "")
                $order = "ORDER BY ".$order;

				
            $res = Model::dbQuery("SELECT * FROM ".$name." ".$order);
            
            if($res)
            {
                while($array = mysql_fetch_array($res))
                {
                    
                    $c =  get_called_class();
                    $o = new $c();
                    $ref = new ReflectionClass($o);
                    
                    foreach($ref->getProperties() as $prop)
                    {
                        $p = $prop->getName();
                        
                        $o->$p= $array[$p];
                       
                    }
                    
                    array_push($objects, $o);
                }
                
                
            }
            else
            {
             echo mysql_error();    
            }
            
            
            return $objects;
            
        }
        
		public static function dbQuery($sql)
		{
			include("db.inc.php");
			
			$sql = mysql_real_escape_string($sql);
		
			mysql_connect($DB_SERVER, $DB_USER, $DB_PWD) or die(mysql_error());
            mysql_select_db($DB_DB) or die(mysql_error());
			
			$res = mysql_query($sql);
			
			return $res;
		}
    }



    
?>