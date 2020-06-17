<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* __string_template__dcc1462a5e135c9f9f9b3242aade04106dd6671b9eec803ce90a7586ae006cc3 */
class __TwigTemplate_e22e9dbdf2a860e6499c04dbed6bed6be88d94e0a42e3769624d67f68ce8860a extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["set" => 1, "if" => 2];
        $filters = ["escape" => 25];
        $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        $context["total"] = 0;
        // line 2
        if (($this->getAttribute(($context["data"] ?? null), "ploegen", []) == "U6")) {
            // line 3
            echo "  ";
            $context["total"] = 125;
        } elseif (($this->getAttribute(        // line 4
($context["data"] ?? null), "ploegen", []) == "U7")) {
            // line 5
            echo "  ";
            $context["total"] = 180;
            echo "  
";
        } elseif (($this->getAttribute(        // line 6
($context["data"] ?? null), "ploegen", []) == "U8-U19")) {
            // line 7
            echo "  ";
            if (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) < 11)) {
                // line 8
                echo "    ";
                $context["total"] = 190;
                // line 9
                echo "  ";
            } elseif ((($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) < 19) && ($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) > 15))) {
                // line 10
                echo "    ";
                $context["total"] = 215;
                // line 11
                echo "  ";
            } elseif (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) > 17)) {
                // line 12
                echo "    ";
                $context["total"] = 220;
                // line 13
                echo "  ";
            } else {
                // line 14
                echo "    ";
                $context["total"] = 210;
                // line 15
                echo "  ";
            }
        } elseif (($this->getAttribute(        // line 16
($context["data"] ?? null), "ploegen", []) == "Keeper")) {
            // line 17
            echo "  ";
            if (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) < 16)) {
                // line 18
                echo "    ";
                $context["total"] = 225;
                // line 19
                echo "  ";
            } elseif (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) > 17)) {
                // line 20
                echo "    ";
                $context["total"] = 240;
                // line 21
                echo "  ";
            } else {
                // line 22
                echo "    ";
                $context["total"] = 235;
                // line 23
                echo "  ";
            }
        }
        // line 25
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["total"] ?? null)), "html", null, true);
        echo "
";
    }

    public function getTemplateName()
    {
        return "__string_template__dcc1462a5e135c9f9f9b3242aade04106dd6671b9eec803ce90a7586ae006cc3";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  122 => 25,  118 => 23,  115 => 22,  112 => 21,  109 => 20,  106 => 19,  103 => 18,  100 => 17,  98 => 16,  95 => 15,  92 => 14,  89 => 13,  86 => 12,  83 => 11,  80 => 10,  77 => 9,  74 => 8,  71 => 7,  69 => 6,  64 => 5,  62 => 4,  59 => 3,  57 => 2,  55 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "__string_template__dcc1462a5e135c9f9f9b3242aade04106dd6671b9eec803ce90a7586ae006cc3", "");
    }
}
