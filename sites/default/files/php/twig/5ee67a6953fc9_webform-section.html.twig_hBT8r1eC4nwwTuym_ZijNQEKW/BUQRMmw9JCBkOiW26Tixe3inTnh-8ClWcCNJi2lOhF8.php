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

/* modules/contrib/webform/templates/webform-section.html.twig */
class __TwigTemplate_3cf4574ef4d6f962f020dc33221632ab6f1319df13ab4434afe2335c39a43a1f extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["set" => 28, "if" => 44];
        $filters = ["escape" => 36];
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
        // line 28
        $context["classes"] = [0 => "js-form-item", 1 => "form-item", 2 => "js-form-wrapper", 3 => "form-wrapper", 4 => "webform-section"];
        // line 36
        echo "<section";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["attributes"] ?? null), "addClass", [0 => ($context["classes"] ?? null)], "method")), "html", null, true);
        echo ">
  ";
        // line 38
        $context["title_classes"] = [0 => "webform-section-title", 1 => ((        // line 40
($context["required"] ?? null)) ? ("js-form-required") : ("")), 2 => ((        // line 41
($context["required"] ?? null)) ? ("form-required") : (""))];
        // line 44
        echo "  ";
        if (($context["title"] ?? null)) {
            // line 45
            echo "    <";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["title_tag"] ?? null)), "html", null, true);
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["title_attributes"] ?? null), "addClass", [0 => ($context["title_classes"] ?? null)], "method")), "html", null, true);
            echo ">";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["title"] ?? null)), "html", null, true);
            echo "</";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["title_tag"] ?? null)), "html", null, true);
            echo ">
  ";
        }
        // line 47
        echo "  <div class=\"webform-section-wrapper\">
    ";
        // line 48
        if (($context["errors"] ?? null)) {
            // line 49
            echo "      <div>
        ";
            // line 50
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["errors"] ?? null)), "html", null, true);
            echo "
      </div>
    ";
        }
        // line 53
        echo "    ";
        if ($this->getAttribute(($context["description"] ?? null), "content", [])) {
            // line 54
            echo "      <div";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute($this->getAttribute(($context["description"] ?? null), "attributes", []), "addClass", [0 => "description"], "method")), "html", null, true);
            echo ">";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["description"] ?? null), "content", [])), "html", null, true);
            echo "</div>
    ";
        }
        // line 56
        echo "    ";
        if (($context["prefix"] ?? null)) {
            // line 57
            echo "      <span class=\"field-prefix\">";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["prefix"] ?? null)), "html", null, true);
            echo "</span>
    ";
        }
        // line 59
        echo "    ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["children"] ?? null)), "html", null, true);
        echo "
    ";
        // line 60
        if (($context["suffix"] ?? null)) {
            // line 61
            echo "      <span class=\"field-suffix\">";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["suffix"] ?? null)), "html", null, true);
            echo "</span>
    ";
        }
        // line 63
        echo "  </div>
</section>
";
    }

    public function getTemplateName()
    {
        return "modules/contrib/webform/templates/webform-section.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  127 => 63,  121 => 61,  119 => 60,  114 => 59,  108 => 57,  105 => 56,  97 => 54,  94 => 53,  88 => 50,  85 => 49,  83 => 48,  80 => 47,  69 => 45,  66 => 44,  64 => 41,  63 => 40,  62 => 38,  57 => 36,  55 => 28,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "modules/contrib/webform/templates/webform-section.html.twig", "/data/sites/web/brightbytesbe/subsites/ploegen.remasport.be/modules/contrib/webform/templates/webform-section.html.twig");
    }
}
