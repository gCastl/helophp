<?php

/* index.html */
class __TwigTemplate_202dd095aafdd391471d44bf42f37b390f24b89a2702fee2d2a650a2c1bd0299 extends Twig_Template
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
        echo "<html>
\t<head>
\t\t<link rel='stylesheet' href='/css/core.css' />
\t\t<link rel='stylesheet' href='/web/css/bootstrap.min.css' />
\t</head>
\t<body>
\t\t<div class='navbar navbar-inverse navbar-fixed-top core-navbar'>
\t\t\t<div class='container'>
\t\t\t\t<div class=\"navbar-header\">
\t\t\t      \t<a href=\"../\" class=\"navbar-brand\">helophp</a>
\t\t\t    </div>
\t\t\t    <div class=\"collapse navbar-collapse bs-navbar-collapse\" role=\"navigation\">
\t\t\t    \t<ul class=\"nav navbar-nav\">
\t\t\t        \t<li>
\t\t\t        \t\t<a href=\"Install\">Installs</a>
\t\t\t        \t</li>
\t\t\t        \t<li class='active'>
\t\t\t          \t\t<a href=\"Docs\">Documentation</a>
\t\t\t        \t</li>
\t\t\t      \t</ul>
\t\t\t      \t<ul class=\"nav navbar-nav navbar-right\">
\t\t\t        \t<li>
\t\t\t          \t\t<a href=\"../about\">Download</a>
\t\t\t        \t</li>
\t\t\t      \t</ul>
\t\t\t    </div>
\t\t\t</div>
\t\t</div>

\t\t<div class=\"jumbotron\">
\t    \t<div class=\"container\">
\t    \t\t<br/>
\t    \t\t<br/>
\t    \t\t<br/>
\t    \t\t<br/>
\t     \t </div>
\t    </div>

\t    <img src='/img/test.png' />
\t</body>
</html>";
    }

    public function getTemplateName()
    {
        return "index.html";
    }

    public function getDebugInfo()
    {
        return array (  19 => 1,);
    }
}
