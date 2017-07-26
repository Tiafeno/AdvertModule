<?php

/**
 * Created by FALI Crea.
 * User: Tiafeno Finel
 * Date: 09/02/2017
 * Time: 05:27 PM
 */

/**
 * @property TWIG Engine
 */
class search_Widget extends WP_Widget
{
    private $Template;

    public function __construct()
    {
      global $TWIG;
      $this->Template = $TWIG;
      parent::__construct("fali_advert", "Advert > Search Bar", array('description' => ''));
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        echo $args['before_title'];
        //echo apply_filters('widget_title', $instance['title']);
        echo $args['after_title'];
        echo $this->Template->render('@frontadvert/search.advert.html', array(
                    'nonce' => wp_nonce_field('search', 'search_nonce')
        ));
        echo $args['after_widget'];

    }

    public function form($instance)
    {
        // $title = isset($instance['title']) ? $instance['title']:'Annonce à la une';
        // $t = new stdClass();
        //     $t->Value = $title;
        //     $t->Name = $this->get_field_name('title');
        //     $t->Id = $this->get_field_id('title');
        //
        // $n = new stdClass();
        //     $n->Value = isset($instance['number']) ? $instance['number']:'5';
        //     $n->Name = $this->get_field_name('number');
        //     $n->Id = $this->get_field_id('number');
        //
        // $this->Engine->assign('title',$t);
        // $this->Engine->assign('number', $n);
        // $this->Engine->display('NewsLetterForm.html.tpl');
    }
}
