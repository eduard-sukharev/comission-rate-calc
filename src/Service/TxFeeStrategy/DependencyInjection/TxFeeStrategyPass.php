<?php

declare(strict_types=1);

namespace App\Service\TxFeeStrategy\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TxFeeStrategyPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(TxFeeStrategyChain::class)) {
            return;
        }

        $inspectorDefinition = $container->findDefinition(TxFeeStrategyChain::class);
        $strategies = $container->findTaggedServiceIds('app.fee_strategy');

        foreach ($strategies as $id => $strategy) {
            $inspectorDefinition->addMethodCall('addStrategy', array(new Reference($id)));
        }
    }
}
