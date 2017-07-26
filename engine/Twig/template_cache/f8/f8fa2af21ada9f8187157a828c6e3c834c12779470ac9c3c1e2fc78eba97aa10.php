<?php

/* @frontadvert/advertaddform.html */
class __TwigTemplate_0b2412761e42b662d19c28b2bd2859d54c4c8364bcad1e2776d06837e4d1129b extends Twig_Template
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
        echo "<div ng-app=\"AdvertApp\" ng-controller=\"AdvertFormAddCtrl\" class=\"ng-scope\">
      <md-content layout-padding>
        <form name=\"setAdvertForm\" ng-submit=\"subscribSubmit(setAdvertForm.\$valid)\" novalidate>
            <div layout=\"row\" layout-sm=\"column\" layout-align=\"space-around\" >
                <md-progress-circular md-mode=\"indeterminate\" ng-show=\"progressbar\" md-diameter=\"40\"></md-progress-circular>
            </div>
           <md-subheader ng-show=\"messages.fr.warn.show\" class=\"md-warn\" style=\"text-align:center\">";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["messages"]) ? $context["messages"] : null), "fr", array()), "warn", array()), "msg", array()), "html", null, true);
        echo "</md-subheader>
           <md-subheader ng-show=\"messages.fr.success.show\" style=\"text-align:center\">";
        // line 8
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["messages"]) ? $context["messages"] : null), "fr", array()), "success", array()), "msg", array()), "html", null, true);
        echo "</md-subheader>
           <md-subheader ng-show=\"messages.fr.exist.show\" class=\"md-warn\" style=\"text-align:center\">";
        // line 9
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute((isset($context["messages"]) ? $context["messages"] : null), "fr", array()), "exist", array()), "msg", array()), "html", null, true);
        echo "</md-subheader>
\t        ";
        // line 10
        echo (isset($context["nonce"]) ? $context["nonce"] : null);
        echo "

            <md-input-container flex=\"50\" class=\"md-block\" >
                <label>Title</label>
                <input ng-cust required name=\"title\" ng-model=\"advertPost.title\">
                <div ng-messages=\"setAdvertForm.title.\$error\">
                    <div ng-message=\"required\">This is required.</div>
                </div>
            </md-input-container>
            
            
            
            <md-input-container class=\"md-block\">
              <label>Description</label>
              <textarea ng-model=\"advertPost.description\" name=\"description\" md-maxlength=\"150\" rows=\"5\" md-select-on-focus require></textarea>
                
            </md-input-container>
            
            <md-input-container flex=\"50\" class=\"md-block\" flex-gt-sm>
              <label>Cost</label>
              <input type=\"number\" name=\"cost\" ng-model=\"advertPost.cost\" ng-pattern=\"/^[0-9]/\" />
                <div ng-messages=\"setAdvertForm.cost.\$error\" role=\"alert\">
                    <div ng-message-exp=\"['number']\">
                        Cost invalid
                    </div>
                </div>
            </md-input-container>

            <md-input-container flex=\"50\" class=\"md-block\" >
                <input id=\"fileInput\" ngf-change=\"uploadFile\" name=\"file\" type=\"file\" class=\"ng-hide\" >
                <md-button id=\"uploadButton\" uploadfile class=\"md-raised md-primary\"> Choose Files </md-button>
             
            </md-input-container>
            <md-content class=\"md-padding\" layout-xs=\"column\" layout=\"row\">
                <div flex-gt-xs=\"25\" layout=\"column\">
                  <md-card>
                    <img ng-src=\"[[imagePath]]\" class=\"md-card-image\" alt=\"Washed Out\">
                  </md-card>
                    <md-card>
                    <img ng-src=\"[[imagePath]]\" class=\"md-card-image\" alt=\"Washed Out\">
                  </md-card>
                </div>
            </md-content>


        <div style=\"margin-top:40px;\">
            <md-button type=\"submit\" ng-disabled=\"setAdvertForm.\$invalid\" ng-click=\"\" style=\"margin:auto; display:block\">Send</md-button>
        </div>

        </form>
    </md-content>
</div>";
    }

    public function getTemplateName()
    {
        return "@frontadvert/advertaddform.html";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  39 => 10,  35 => 9,  31 => 8,  27 => 7,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("<div ng-app=\"AdvertApp\" ng-controller=\"AdvertFormAddCtrl\" class=\"ng-scope\">
      <md-content layout-padding>
        <form name=\"setAdvertForm\" ng-submit=\"subscribSubmit(setAdvertForm.\$valid)\" novalidate>
            <div layout=\"row\" layout-sm=\"column\" layout-align=\"space-around\" >
                <md-progress-circular md-mode=\"indeterminate\" ng-show=\"progressbar\" md-diameter=\"40\"></md-progress-circular>
            </div>
           <md-subheader ng-show=\"messages.fr.warn.show\" class=\"md-warn\" style=\"text-align:center\">{{messages.fr.warn.msg}}</md-subheader>
           <md-subheader ng-show=\"messages.fr.success.show\" style=\"text-align:center\">{{messages.fr.success.msg}}</md-subheader>
           <md-subheader ng-show=\"messages.fr.exist.show\" class=\"md-warn\" style=\"text-align:center\">{{messages.fr.exist.msg}}</md-subheader>
\t        {{nonce|raw}}

            <md-input-container flex=\"50\" class=\"md-block\" >
                <label>Title</label>
                <input ng-cust required name=\"title\" ng-model=\"advertPost.title\">
                <div ng-messages=\"setAdvertForm.title.\$error\">
                    <div ng-message=\"required\">This is required.</div>
                </div>
            </md-input-container>
            
            
            
            <md-input-container class=\"md-block\">
              <label>Description</label>
              <textarea ng-model=\"advertPost.description\" name=\"description\" md-maxlength=\"150\" rows=\"5\" md-select-on-focus require></textarea>
                
            </md-input-container>
            
            <md-input-container flex=\"50\" class=\"md-block\" flex-gt-sm>
              <label>Cost</label>
              <input type=\"number\" name=\"cost\" ng-model=\"advertPost.cost\" ng-pattern=\"/^[0-9]/\" />
                <div ng-messages=\"setAdvertForm.cost.\$error\" role=\"alert\">
                    <div ng-message-exp=\"['number']\">
                        Cost invalid
                    </div>
                </div>
            </md-input-container>

            <md-input-container flex=\"50\" class=\"md-block\" >
                <input id=\"fileInput\" ngf-change=\"uploadFile\" name=\"file\" type=\"file\" class=\"ng-hide\" >
                <md-button id=\"uploadButton\" uploadfile class=\"md-raised md-primary\"> Choose Files </md-button>
             
            </md-input-container>
            <md-content class=\"md-padding\" layout-xs=\"column\" layout=\"row\">
                <div flex-gt-xs=\"25\" layout=\"column\">
                  <md-card>
                    <img ng-src=\"[[imagePath]]\" class=\"md-card-image\" alt=\"Washed Out\">
                  </md-card>
                    <md-card>
                    <img ng-src=\"[[imagePath]]\" class=\"md-card-image\" alt=\"Washed Out\">
                  </md-card>
                </div>
            </md-content>


        <div style=\"margin-top:40px;\">
            <md-button type=\"submit\" ng-disabled=\"setAdvertForm.\$invalid\" ng-click=\"\" style=\"margin:auto; display:block\">Send</md-button>
        </div>

        </form>
    </md-content>
</div>", "@frontadvert/advertaddform.html", "D:\\server\\htdocs\\netpositiveimpact\\wp-content\\plugins\\atomisy-advert\\engine\\Twig\\templates\\front\\advertaddform.html");
    }
}
