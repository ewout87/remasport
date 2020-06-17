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

/* __string_template__ccdfee6061ae7ec5f10e4d62b1b1967e0ebdbb7d5be51cec27cab136fe881cc8 */
class __TwigTemplate_a5c796e0197ffb24efa5de82515993211a022178cf5b58def276fdf7e25b8ee6 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["set" => 1, "if" => 2];
        $filters = ["escape" => 23];
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
            $context["total"] = 165;
            echo "  
";
        } elseif ((($this->getAttribute(        // line 6
($context["data"] ?? null), "ploegen", []) == "U8-U19") && ($this->getAttribute(($context["data"] ?? null), "lidmaatschap", []) == "Ik ben een nieuw lid"))) {
            // line 7
            echo "  ";
            if (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) < 11)) {
                // line 8
                echo "    ";
                $context["total"] = 180;
                // line 9
                echo "  ";
            } elseif (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) > 17)) {
                // line 10
                echo "    ";
                $context["total"] = 195;
                // line 11
                echo "  ";
            } else {
                // line 12
                echo "    ";
                $context["total"] = 190;
                // line 13
                echo "  ";
            }
        } elseif ((($this->getAttribute(        // line 14
($context["data"] ?? null), "ploegen", []) == "Keeper") && ($this->getAttribute(($context["data"] ?? null), "lidmaatschap", []) == "Ik ben een nieuw lid"))) {
            // line 15
            echo "  ";
            if (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) < 16)) {
                // line 16
                echo "    ";
                $context["total"] = 190;
                // line 17
                echo "  ";
            } elseif (($this->getAttribute(($context["data"] ?? null), "ploeg_u8_u19", []) > 17)) {
                // line 18
                echo "    ";
                $context["total"] = 200;
                // line 19
                echo "  ";
            } else {
                // line 20
                echo "    ";
                $context["total"] = 195;
                // line 21
                echo "  ";
            }
        }
        // line 23
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["total"] ?? null)), "html", null, true);
        echo "
";
    }

    public function getTemplateName()
    {
        return "__string_template__ccdfee6061ae7ec5f10e4d62b1b1967e0ebdbb7d5be51cec27cab136fe881cc8";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  116 => 23,  112 => 21,  109 => 20,  106 => 19,  103 => 18,  100 => 17,  97 => 16,  94 => 15,  92 => 14,  89 => 13,  86 => 12,  83 => 11,  80 => 10,  77 => 9,  74 => 8,  71 => 7,  69 => 6,  64 => 5,  62 => 4,  59 => 3,  57 => 2,  55 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "__string_template__ccdfee6061ae7ec5f10e4d62b1b1967e0ebdbb7d5be51cec27cab136fe881cc8", "");
    }
}
