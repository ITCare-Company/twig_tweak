<?php

namespace Drupal\twig_tweak\Command;

use Symfony\Component\Finder\Finder;

/**
 * Implements twig-tweak:lint console command.
 */
final class TwigLintCommand extends LintCommand {

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'twig-tweak:validate';

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {

    if (!\class_exists(Finder::class)) {
      throw new \LogicException('To validate Twig templates you must install symfony/finder component.');
    }

    parent::configure();
    $this->setAliases(['twig-validate']);

    $help = <<< 'TEXT'

      This command only validates Twig Syntax. For checking code style
      consider using <info>friendsoftwig/twigcs</info> package.
      TEXT;

    $this->setHelp($this->getHelp() . $help);
  }

}
