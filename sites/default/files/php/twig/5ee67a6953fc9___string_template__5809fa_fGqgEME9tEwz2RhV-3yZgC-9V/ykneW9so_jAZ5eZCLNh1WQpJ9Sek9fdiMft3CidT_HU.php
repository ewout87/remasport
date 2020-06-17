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

/* __string_template__5809fa6593f26375a19dc16816639a5448dd8c82e1b1d6ebb189257699193f7b */
class __TwigTemplate_610f0f2bce27acfd2c14973c3ebdb7c90fed2a0a690f8c1a487c20b195f4626c extends \Twig\Template
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
        return "__string_template__5809fa6593f26375a19dc16816639a5448dd8c82e1b1d6ebb189257699193f7b";
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
        return new Source("", "__string_template__5809fa6593f26375a19dc16816639a5448dd8c82e1b1d6ebb189257699193f7b", "");
    }
}
