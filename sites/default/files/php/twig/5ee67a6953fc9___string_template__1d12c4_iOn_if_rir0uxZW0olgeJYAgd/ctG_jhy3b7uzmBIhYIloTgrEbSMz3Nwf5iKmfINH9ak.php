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

/* __string_template__1d12c4e969014fa12bddaa14e5e8df85beda3a880b5f496c8fc9e59c8c3fe80e */
class __TwigTemplate_0e93afadc12199e9b0a23b9256f37930ee33e7f713949f14ad3c3f8640f5e2d1 extends \Twig\Template
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
        $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["data"] ?? null), "presentatiebroek_8450_08", []));
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 3
            if ($this->getAttribute($context["item"], "maat", [])) {
                // line 4
                if (((((((((((($this->getAttribute($context["item"], "maat", []) == 104) || ($this->getAttribute($context["item"], "maat", []) == 110)) || ($this->getAttribute($context["item"], "maat", []) == 116)) || ($this->getAttribute($context["item"], "maat", []) == 122)) || ($this->getAttribute($context["item"], "maat", []) == 128)) || ($this->getAttribute($context["item"], "maat", []) == 134)) || ($this->getAttribute($context["item"], "maat", []) == 140)) || ($this->getAttribute($context["item"], "maat", []) == 146)) || ($this->getAttribute($context["item"], "maat", []) == 152)) || ($this->getAttribute($context["item"], "maat", []) == 158)) || ($this->getAttribute($context["item"], "maat", []) == 164))) {
                    // line 5
                    echo "  ";
                    $context["total"] = (($context["total"] ?? null) + ($this->getAttribute($context["item"], "aantal", []) * 21));
                    // line 6
                    echo "  ";
                } else {
                    // line 7
                    echo "  ";
                    $context["total"] = (($context["total"] ?? null) + ($this->getAttribute($context["item"], "aantal", []) * 25));
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
        return "__string_template__1d12c4e969014fa12bddaa14e5e8df85beda3a880b5f496c8fc9e59c8c3fe80e";
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
        return new Source("", "__string_template__1d12c4e969014fa12bddaa14e5e8df85beda3a880b5f496c8fc9e59c8c3fe80e", "");
    }
}
