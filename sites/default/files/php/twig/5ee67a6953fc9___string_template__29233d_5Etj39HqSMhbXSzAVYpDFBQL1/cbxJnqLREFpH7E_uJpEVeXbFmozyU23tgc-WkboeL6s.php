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

/* __string_template__29233d5978ea613bb28ad77d5267691824f7d9e8f057595974d822327fde9c43 */
class __TwigTemplate_8c84f5b6e18a9fa50667ca384ce3639aea6e8024c0bb6891f94b969220d4bf3f extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["set" => 1, "for" => 2, "if" => 3];
        $filters = ["escape" => 11];
        $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'for', 'if'],
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
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["data"] ?? null), "thermoshirt_6455_00", []));
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 3
            if ($this->getAttribute($context["item"], "maat", [])) {
                // line 4
                if (((($this->getAttribute($context["item"], "maat", []) == "3XS") || ($this->getAttribute($context["item"], "maat", []) == "2XS")) || ($this->getAttribute($context["item"], "maat", []) == "XS"))) {
                    // line 5
                    echo "  ";
                    $context["total"] = (($context["total"] ?? null) + ($this->getAttribute($context["item"], "aantal", []) * 21));
                    // line 6
                    echo "  ";
                } else {
                    // line 7
                    echo "  ";
                    $context["total"] = (($context["total"] ?? null) + ($this->getAttribute($context["item"], "aantal", []) * 24.5));
                }
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 11
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["total"] ?? null)), "html", null, true);
        echo "
";
    }

    public function getTemplateName()
    {
        return "__string_template__29233d5978ea613bb28ad77d5267691824f7d9e8f057595974d822327fde9c43";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  80 => 11,  71 => 7,  68 => 6,  65 => 5,  63 => 4,  61 => 3,  57 => 2,  55 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "__string_template__29233d5978ea613bb28ad77d5267691824f7d9e8f057595974d822327fde9c43", "");
    }
}
