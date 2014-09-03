<?php
    require_once('db.php');
    require_once('config.php');
    require_once ("MiniTemplator.class.php"); 
    
    /* Since part D was all about using PDO, this is the only file that
     * has any DB interaction so I'm pretty sure no other file is different.
     * from part C.
     */
    
    // connect to the DB
    try{
        $dbconn = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8',
                    DB_USER, DB_PW);
    } catch(PDOException $e) {
        echo 'Could not connect to mysql database ' . DB_NAME . ' on ' . DB_HOST . '\n';
    }
    
    /* Gets the possible values for the respective 
     * field and returns it as an array.
     */
    function get_values_of_field($field){
        $query = "";
        if($field == 'grape'){
            $query = "SELECT variety FROM grape_variety";
        } elseif($field == 'region'){
            $query = "SELECT region_name FROM region";
        } else{
            // $field == 'to_year' || 'from_year'
            $query = "SELECT DISTINCT year FROM wine ORDER BY year DESC";
        }
        
        global $dbconn;
        $i = 0;
        $values = array();
        
        foreach($dbconn->query($query) as $row)
                $values[$i++] = $row[0];
        
        return $values;
    }
    
    /* Given $query_array is just $_GET, so the function just
     * builds the query given the user's search parameters and
     * returns the results as an array.
     */
    function get_results($query_array){
        global $dbconn;
        $query_result = array();
        $i = 0;
        
        $select_clause = "SELECT wine_name, variety, year, winery_name, region_name, cost, on_hand, SUM(qty) AS num_sold, SUM(price) AS revenue ";
        
        $from_clause = "FROM wine, wine_variety, grape_variety, winery, region, inventory, items ";
        
        $where_clause = "WHERE wine.wine_id = wine_variety.wine_id " .
                        "AND wine_variety.variety_id = grape_variety.variety_id " .
                        "AND wine.winery_id = winery.winery_id " .
                        "AND winery.region_id = region.region_id " .
                        "AND wine.wine_id = inventory.wine_id " .
                        "AND wine.wine_id = items.wine_id ";
        
        if($query_array['wine_name'] != ""){
            $where_clause .= "AND wine_name LIKE \"%{$query_array['wine_name']}%\" ";
        }
        
        if($query_array['winery_name'] != ""){
            $where_clause .= "AND winery_name LIKE \"%{$query_array['winery_name']}%\" ";
        }
        
        if($query_array['region'] != "All"){
            $where_clause .= "AND region_name = \"{$query_array['region']}\" ";
        }
        
        $where_clause .= "AND variety = \"{$query_array['grape']}\" ";

        if($query_array['from_year'] != "default"){
            $where_clause .= "AND year >= {$query_array['from_year']} ";
        }
        
        if($query_array['to_year'] != "default"){
            $where_clause .= "AND year <= {$query_array['to_year']} ";
        }
        
        if($query_array['min_stock'] != ""){
            $where_clause .= "AND on_hand >= {$query_array['min_stock']} ";
        }
        
        if($query_array['min_ordered'] != ""){
            $where_clause .= "AND num_sold >= {$query_array['min_ordered']} ";
        }
        
        if($query_array['min_cost'] != ""){
            $where_clause .= "AND revenue >= {$query_array['min_cost']} ";
        }
        
        if($query_array['max_cost'] != ""){
            $where_clause .= "AND revenue <= {$query_array['max_cost']} ";
        }
        
        $group_by_clause = "GROUP BY wine.wine_id, grape_variety.variety_id ";
        
        $order_by_clause = "ORDER BY year;";
        
        $query = $select_clause .
                 $from_clause .
                 $where_clause .
                 $group_by_clause .
                 $order_by_clause;
        
        foreach($dbconn->query($query) as $row){
                $query_result[$i++] = $row;
        }
            
        return $query_result;
    }
?>