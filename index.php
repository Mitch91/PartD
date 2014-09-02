<?php
    require_once('model.php');
    
    function populate_dropdown_for_field($field){
        global $query_templator;
        
        $values = get_values_of_field($field);
        foreach($values as $value){
            $query_templator->setVariable($field, $value);
            $query_templator->addBlock($field);
        }
    }
    
    $query_templator = new MiniTemplator;
    $query_templator->readTemplateFromFile("search_template.html");
    
    populate_dropdown_for_field("region");
    populate_dropdown_for_field("grape");
    populate_dropdown_for_field("from_year");
    populate_dropdown_for_field("to_year");
    
    if(isset($_GET['error_msg'])){
        $query_templator->setVariable("error_msg", $_GET['error_msg']);
        $query_templator->addBlock("error_msg");
    }
    $query_templator->generateOutput();
?>