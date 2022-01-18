<?php

namespace FluentForm\App\Services\Migrator;

class CalderaMigrator extends BaseMigrator
{
    public function exist()
    {
        return defined( 'CFCORE_VER' );
    }

    public function getForms()
    {
        $forms = [];

        $items = \Caldera_Forms_Forms::get_forms();

        foreach ( $items as $item ) {
            $forms[] = \Caldera_Forms_Forms::get_form( $item );
        }

        return $forms;
    }

    public function getFields($form)
    {
        $fields      = \Caldera_Forms_Forms::get_fields( $form );
        $formattedFields = [];

        foreach ($fields as $field)
        {

        }


        return $fields;
    }
}