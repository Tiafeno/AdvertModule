<?php

/* @frontmaps/maps.html */
class __TwigTemplate_85813890b234863c5d9a37ab7d31718daddb874f1facfa673bac1e05a9d724cd extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<section layout=\"row\" layout-sm=\"column\" layout-align=\"left left\" layout-wrap 
         style=\"position: fixed; z-index: 999; padding:0\">
\t\t <md-button ng-click=\"toggleRight()\" class=\"md-raised md-primary\">
            <i class=\"material-icons\">menu</i>
          </md-button>
        <md-button class=\"md-raised\" ng-click=\"__init__()\">Start Again</md-button>
\t\t
</section>


<section layout=\"row\" flex style=\"display: initial;\">
 <md-sidenav class=\"md-sidenav-right\" md-component-id=\"right\"
                md-disable-backdrop md-whiteframe=\"8\">

      <md-toolbar class=\"md-theme-indigo\">
        <h1 class=\"md-toolbar-tools\">[[title]]</h1>
      </md-toolbar>

      <md-content layout-margin>
\t\t<p class=\"md-subhead\" style=\"text-align:center\">Please select from below or use the map to zoom or select</p>
        <div layout=\"row\" style=\"margin-top:40px\">
            <md-input-container class=\"md-block\" flex style=\"margin:0 auto\">
                <label>State</label>
                <md-select ng-model=\"state\">
                    <md-option ng-repeat=\"state in states\" ng-value=\"state.slug\">
                        [[state.name]]
                    </md-option>
                </md-select>
            </md-input-container>
        </div>

        <section layout=\"row\">
            <md-list layout=\"column\" layout-padding >
                <md-list-item class=\"md-3-line\" ng-repeat=\"product in products\" style=\"background: lemonchiffon; padding-top: 10px; padding-bottom: 10px\">
                    <img ng-src=\"[[product.thumb_url]]\" class=\"md-avatar\" alt=\"[[product.title]]\">
                    <div class=\"md-list-item-text\">
                        <a href=\"[[product.link]]\" title=\"[[product.title]]\" target=\"_blank\">
                            <h3>[[product.title]]</h3>
                        </a>
                        <h6 style=\"font-size: 12px; margin: 4px 0px\">[[product.city[0].name]]</h6>
                        <p ng-bind-html=\"product.content | limitTo:88\" ></p>
                    </div>
                </md-list-item>
            </md-list>
        </section>
\t\t
        <md-button ng-click=\"toggleRight()\" class=\"md-accent\">
          Close
        </md-button>
      </md-content>

 </md-sidenav>
</section>


<div id=\"map\" ></div>";
    }

    public function getTemplateName()
    {
        return "@frontmaps/maps.html";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("<section layout=\"row\" layout-sm=\"column\" layout-align=\"left left\" layout-wrap 
         style=\"position: fixed; z-index: 999; padding:0\">
\t\t <md-button ng-click=\"toggleRight()\" class=\"md-raised md-primary\">
            <i class=\"material-icons\">menu</i>
          </md-button>
        <md-button class=\"md-raised\" ng-click=\"__init__()\">Start Again</md-button>
\t\t
</section>


<section layout=\"row\" flex style=\"display: initial;\">
 <md-sidenav class=\"md-sidenav-right\" md-component-id=\"right\"
                md-disable-backdrop md-whiteframe=\"8\">

      <md-toolbar class=\"md-theme-indigo\">
        <h1 class=\"md-toolbar-tools\">[[title]]</h1>
      </md-toolbar>

      <md-content layout-margin>
\t\t<p class=\"md-subhead\" style=\"text-align:center\">Please select from below or use the map to zoom or select</p>
        <div layout=\"row\" style=\"margin-top:40px\">
            <md-input-container class=\"md-block\" flex style=\"margin:0 auto\">
                <label>State</label>
                <md-select ng-model=\"state\">
                    <md-option ng-repeat=\"state in states\" ng-value=\"state.slug\">
                        [[state.name]]
                    </md-option>
                </md-select>
            </md-input-container>
        </div>

        <section layout=\"row\">
            <md-list layout=\"column\" layout-padding >
                <md-list-item class=\"md-3-line\" ng-repeat=\"product in products\" style=\"background: lemonchiffon; padding-top: 10px; padding-bottom: 10px\">
                    <img ng-src=\"[[product.thumb_url]]\" class=\"md-avatar\" alt=\"[[product.title]]\">
                    <div class=\"md-list-item-text\">
                        <a href=\"[[product.link]]\" title=\"[[product.title]]\" target=\"_blank\">
                            <h3>[[product.title]]</h3>
                        </a>
                        <h6 style=\"font-size: 12px; margin: 4px 0px\">[[product.city[0].name]]</h6>
                        <p ng-bind-html=\"product.content | limitTo:88\" ></p>
                    </div>
                </md-list-item>
            </md-list>
        </section>
\t\t
        <md-button ng-click=\"toggleRight()\" class=\"md-accent\">
          Close
        </md-button>
      </md-content>

 </md-sidenav>
</section>


<div id=\"map\" ></div>", "@frontmaps/maps.html", "D:\\server\\htdocs\\netpositiveimpact\\wp-content\\plugins\\atomisy_maps\\Engine\\Twig\\templates\\front\\maps.html");
    }
}
