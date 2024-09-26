<?php

use Phiki\GrammarRepository;

describe('GrammarRepository', function () {
    it('can be constructed', function () {
        expect(new GrammarRepository)->toBeInstanceOf(GrammarRepository::class);
    });

    it('can check for the existence of a grammar', function () {
        $grammarRepository = new GrammarRepository;

        expect($grammarRepository->has('php'))->toBeTrue();
    });

    it('can get a grammar', function () {
        $grammarRepository = new GrammarRepository;
        $grammar = $grammarRepository->get('php');

        expect($grammar)
            ->toBeArray()
            ->toHaveKey('scopeName', 'source.php');
    });

    it('can register a custom grammar using a file path', function () {
        $grammarRepository = new GrammarRepository;
        $grammarRepository->register('example', __DIR__ . '/../Fixtures/example.json');

        $grammar = $grammarRepository->get('example');

        expect($grammar)
            ->toBeArray()
            ->toHaveKey('scopeName', 'source.example');
    });

    it('can register a custom grammar using a grammar array', function () {
        $grammarRepository = new GrammarRepository;
        $grammarRepository->register('example', [
            'scopeName' => 'source.example'
        ]);

        $grammar = $grammarRepository->get('example');

        expect($grammar)
            ->toBeArray()
            ->toHaveKey('scopeName', 'source.example');
    });
});