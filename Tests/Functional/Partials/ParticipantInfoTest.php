<?php

declare(strict_types=1);

namespace FGTCLB\T3oodle\Tests\Functional\Partials;

use FGTCLB\T3oodle\Domain\Model\PollFrontendUser;
use FGTCLB\T3oodle\Domain\Model\Vote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Mvc\ExtbaseRequestParameters;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ParticipantInfoTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'typo3/cms-fluid-styled-content',
    ];

    protected array $testExtensionsToLoad = [
        'fgtclb/t3oodle',
        'georgringer/numbered-pagination',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/FrontendUser.csv');
        // The name and mail of a frontend user are read with the fields configured in the TypoScript settings
        $this->setTypoScriptSettingsOfVote(['frontendUserNameField' => 'name', 'frontendUserMailField' => 'email']);
    }

    protected function tearDown(): void
    {
        $this->setTypoScriptSettingsOfVote([]);
        (new \ReflectionProperty(Vote::class, 'userRowCache'))->setValue(null, []);
        unset($GLOBALS['TYPO3_REQUEST']);
        parent::tearDown();
    }

    public static function guestNoticeDataProvider(): \Generator
    {
        yield 'guest' => [
            'participant' => false,
            'participantMail' => '',
            'outputParticipantMails' => true,
            'outputGuestNotice' => true,
            'expectedMailLink' => false,
            'expectedGuestNotice' => true,
        ];
        yield 'guest with mail' => [
            'participant' => false,
            'participantMail' => 'guest@example.org',
            'outputParticipantMails' => true,
            'outputGuestNotice' => true,
            'expectedMailLink' => true,
            'expectedGuestNotice' => true,
        ];
        yield 'frontend user' => [
            'participant' => true,
            'participantMail' => '',
            'outputParticipantMails' => false,
            'outputGuestNotice' => true,
            'expectedMailLink' => false,
            'expectedGuestNotice' => false,
        ];
        yield 'frontend user with mail' => [
            'participant' => true,
            'participantMail' => '',
            'outputParticipantMails' => true,
            'outputGuestNotice' => true,
            'expectedMailLink' => true,
            'expectedGuestNotice' => false,
        ];
        yield 'guest without guest notice' => [
            'participant' => false,
            'participantMail' => '',
            'outputParticipantMails' => true,
            'outputGuestNotice' => false,
            'expectedMailLink' => false,
            'expectedGuestNotice' => false,
        ];
    }

    #[DataProvider('guestNoticeDataProvider')]
    #[Test]
    public function guestNoticeIsShownForGuestsOnly(
        bool $participant,
        string $participantMail,
        bool $outputParticipantMails,
        bool $outputGuestNotice,
        bool $expectedMailLink,
        bool $expectedGuestNotice
    ): void {
        $vote = new Vote();
        $vote->setParticipantName('Jo Guest');
        $vote->setParticipantMail($participantMail);
        if ($participant) {
            $frontendUser = new PollFrontendUser();
            $frontendUser->_setProperty('uid', 1);
            $vote->setParticipant($frontendUser);
        }

        $output = $this->renderParticipantInfo($vote, [
            'outputGuestNotice' => $outputGuestNotice,
            'outputParticipantMails' => $outputParticipantMails,
        ]);

        // the name of a frontend user comes from its fe_users record, the name of a guest from the vote
        self::assertStringContainsString($participant ? 'Jane Doe' : 'Jo Guest', $output);
        self::assertSame($expectedMailLink, str_contains($output, 'mailto:'));
        self::assertSame($expectedGuestNotice, str_contains($output, '<span class="text-guest">(guest)</span>'));
    }

    /**
     * @param array<string, string> $settings
     */
    private function setTypoScriptSettingsOfVote(array $settings): void
    {
        (new \ReflectionProperty(Vote::class, 'typoscriptSettings'))->setValue(null, $settings);
    }

    /**
     * @param array<string, bool> $settings
     */
    private function renderParticipantInfo(Vote $vote, array $settings): string
    {
        $extbaseRequestParameters = (new ExtbaseRequestParameters())->setControllerExtensionName('T3oodle');
        $serverRequest = (new ServerRequest())
            ->withAttribute('extbase', $extbaseRequestParameters)
            // the backend request type renders the labels in the default language without a site
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $GLOBALS['TYPO3_REQUEST'] = $serverRequest;

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->setRequest(new Request($serverRequest));
        $context->getTemplatePaths()->setPartialRootPaths([
            ExtensionManagementUtility::extPath('t3oodle') . 'Resources/Private/Partials/',
        ]);
        $context->getTemplatePaths()->setTemplateSource(
            '<f:render partial="Poll/Voting/ParticipantInfo" arguments="{vote: vote, settings: settings}" />'
        );
        $view = new TemplateView($context);
        $view->assignMultiple(['vote' => $vote, 'settings' => $settings]);

        return (string)$view->render();
    }
}
