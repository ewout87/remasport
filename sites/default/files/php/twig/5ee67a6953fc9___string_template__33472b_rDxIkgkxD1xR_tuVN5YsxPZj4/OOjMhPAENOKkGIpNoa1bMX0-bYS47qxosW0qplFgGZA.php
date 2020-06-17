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

/* __string_template__33472b6b91db3ddd6261edf893f4086d4d36e20d0a15975d78e800972f26d694 */
class __TwigTemplate_a9ce1ac088dede606899464b4032794dacf49b4a67efbfbaac2d3479a2f898ed extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["if" => 1, "set" => 2, "for" => 3];
        $filters = ["escape" => 8];
        $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if', 'set', 'for'],
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
        if ($this->getAttribute(($context["data"] ?? null), "keeper_underwear_lange_mouwen_8965_08", [])) {
            // line 2
            $context["total"] = 0;
            // line 3
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["data"] ?? null), "keeper_underwear_lange_mouwen_8965_08", []));
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                // line 4
                if ($this->getAttribute($context["item"], "maat", [])) {
                    // line 5
                    echo "    ";
                    $context["total"] = (($context["total"] ?? null) + ($this->getAttribute($context["item"], "aantal", []) * 40));
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 8
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["total"] ?? null)), "html", null, true);
            echo "
";
        }
    }

    public function getTemplateName()
    {
        return "__string_template__33472b6b91db3ddd6261edf893f4086d4d36e20d0a15975d78e800972f26d694";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  73 => 8,  65 => 5,  63 => 4,  59 => 3,  57 => 2,  55 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "__string_template__33472b6b91db3ddd6261edf893f4086d4d36e20d0a15975d78e800972f26d694", "");
    }
}
