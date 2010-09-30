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
           
            $props = array();
            $values = array();  
			$types = array();
             
			foreach($ref->getProperties() as $prop)
			{
				if($prop->getName() != "id")
				{
					array_push($props, $prop->getName());
					array_push($types, gettype($prop->getValue($this)));
					array_push($values, $prop->getValue($this));
				}   
			}
            
			$typedef = Model::createTypeDef($props, $values, $types);

            
            // Create table if needed
            if(mysql_num_rows( Model::dbQuery("SHOW TABLES LIKE '".$name."'"))<=0)
            {
                $varsarray = array();
					
				foreach($typedef as $td)
				{
					array_push($varsarray, $td["name"]." ".$td["dbtype"]);	
				}
					
				$vars = implode(", ", $varsarray);
				
				$sql = "CREATE TABLE ".$name." ( "; 
                $sql .= "`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
				$sql .= $vars;
                $sql .= ")";

              
                
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
			$vals = array();
			
			for($i = 0; $i < sizeof($typedef); $i++)
			{
				$td = $typedef[$i];
				$vals[$i] = $td["value"];
			}

            if($this->id >= 0)
                $sql = "INSERT INTO ".$name."(id, ".implode(",", $props).") VALUES(".$this->id.", ".implode(",", $vals).")";
            else
                $sql = "INSERT INTO ".$name."(".implode(",", $props).") VALUES(".implode(",", $vals).")";
            
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
        
		public function createTypeDef($props, $values, $types)
		{
			$typedef = array();
			
			$ts = array("boolean" => "BOOL", "integer" => "INT", "double" => "FLOAT", 
							"string" => "VARCHAR 255", "array" => "TEXT", "object" => "TEXT", 
							 "resource" => "TEXT", "NULL" => "TEXT", "unknown type" => "TEXT");
			
			if(sizeof($props) == sizeof($values) && sizeof($props) == sizeof($types))
			{
				$size = sizeof($props);
				
				for($i = 0; $i < $size; $i++)
				{
					$val = $values[$i];
					$dbt = $ts[$types[$i]];
					
					if($dbt == "TEXT")
						$val = serialize($val);
					if($dbt == "VARCHAR 255")
						$val = "\"".$val."\"";

					
					$ar = array("name" => $props[$i], "value" => $val, "type" => $types[$i], "dbtype" => $dbt); 
					
					array_push($typedef, $ar);
				}
			}
			
			
			return $typedef;
		}
		
		public static function dbQuery($sql)
		{
			include("db.inc.php");

			mysql_connect($DB_SERVER, $DB_USER, $DB_PWD) or die(mysql_error());
            mysql_select_db($DB_DB) or die(mysql_error());
			
			//$sql = mysql_real_escape_string($sql);
			
			$res = mysql_query($sql);
			
			return $res;
		}
    }



    
?>