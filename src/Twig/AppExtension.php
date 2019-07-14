<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('snake', [$this, 'snake']),
            new TwigFilter('compile_tmpl', [$this, 'compile'], ['is_safe' => ['html']]),
            new TwigFilter('compile_decimal_tmpl', [$this, 'compileDecimal'], ['is_safe' => ['html']]),
            new TwigFilter('compile_date_tmpl', [$this, 'compileDate'], ['is_safe' => ['html']]),
            new TwigFilter('compile_money_tmpl', [$this, 'compileMoney'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('snake', [$this, 'snake']),
            new TwigFunction('compile_tmpl', [$this, 'compile']),
            new TwigFunction('compile_decimal_tmpl', [$this, 'compileDecimal']),
            new TwigFunction('compile_date_tmpl', [$this, 'compileDate']),
            new TwigFunction('compile_money_tmpl', [$this, 'compileMoney']),
        ];
    }

    public function snake($value)
    {
        $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '_', $value));

        return strtolower($value);
    }

    public function compile($value, $tmpl = false)
    {
        if ($tmpl) {
            return "<%= $value %>";
        }

        return $value;
    }

    public function compileDecimal($value, $tmpl = false)
    {
        if ($tmpl) {
            return "<% if (!isNaN($value) && $value) { %><%= $value.toFixed(2).toString().replace(/\./g, \",\") %><% } else { %><%= $value %><% } %>";
        }

        return $value;
    }

    public function compileDate($value, $tmpl = false)
    {
        if ($tmpl) {
            return "<% if ($value) { %><%= moment(new Date($value)).format('DD/MM/YYYY') %><% } %>";
        }

        return $value;
    }

    public function compileMoney($value, $tmpl = false)
    {
        if ($tmpl) {
            return "
                <% if ($value) { %> 
                    <% if ($value.currency.currencyCode == 'USD') { %>
                        <%= $value.preciseValue.toFixed(2).toString().replace(/\./g, \",\") %> $
                    <% } else { %>
                        &euro; <%= $value.preciseValue.toFixed(2).toString().replace(/\./g, \",\") %>
                    <% } %>
                <% } %>";
        }

        return $value;
    }
}
