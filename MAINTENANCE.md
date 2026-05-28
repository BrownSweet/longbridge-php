# Maintenance Commitment / 维护承诺

This project is maintained as a community PHP SDK for Longbridge OpenAPI.

本项目作为长桥 OpenAPI 的社区 PHP SDK 进行维护。

## Scope / 维护范围

- Keep the SDK installable through Composer and compatible with supported PHP versions.
- Maintain HTTP API wrappers, OAuth helpers, legacy signing, WebSocket/protobuf quote APIs, and trade push APIs.
- Keep generated protobuf classes in sync with the protobuf definitions used by this repository.
- Keep examples, tests, and coverage documents reasonably aligned with the current public API surface.

- 保持 SDK 可通过 Composer 安装，并兼容声明支持的 PHP 版本。
- 维护 HTTP API 封装、OAuth 辅助能力、legacy 签名、WebSocket/protobuf 行情能力和交易推送能力。
- 保持生成的 protobuf 类与本仓库使用的 proto 定义一致。
- 保持示例、测试和覆盖文档与当前公开 API 能力基本一致。

## Compatibility / 兼容性

- The minimum PHP version is defined in `composer.json`.
- Backward-compatible additions may be released in minor versions.
- Breaking changes should be reserved for major versions, documented in the release notes, and avoided unless they remove unsafe behavior or align the SDK with official API changes.
- Deprecated method aliases may be kept for at least one minor release when practical.

- 最低 PHP 版本以 `composer.json` 为准。
- 向后兼容的新功能可以在小版本中发布。
- 破坏性变更应尽量只放在大版本中，并在 release notes 中说明；除非是修复不安全行为或对齐官方 API 变化，否则应避免破坏性变更。
- 可行时，废弃的方法别名至少保留一个小版本周期。

## Testing Policy / 测试策略

- Unit and API wrapper tests should be runnable without Longbridge credentials.
- Real HTTP and WebSocket integration tests must be opt-in through environment variables.
- Account-mutating tests, such as order placement, DCA updates, alert creation, or watchlist changes, must not run by default.
- CI should run Composer validation and the non-integration PHPUnit suite on supported PHP versions.

- 单元测试和 API wrapper 测试应不依赖长桥账号即可运行。
- 真实 HTTP 和 WebSocket 集成测试必须通过环境变量显式开启。
- 会修改账户状态的测试，例如下单、定投修改、提醒创建、自选股修改，默认不得运行。
- CI 应在支持的 PHP 版本上运行 Composer 校验和非集成 PHPUnit 测试。

## Security / 安全

- Do not commit credentials, access tokens, refresh tokens, private keys, or account identifiers.
- Do not log request headers or payloads that may contain credentials or private account data.
- Security-sensitive reports should be handled privately first when possible.
- If a vulnerability is confirmed, the fix should be released with a clear note describing impact and upgrade guidance.

- 不提交凭证、access token、refresh token、私钥或账户标识。
- 不记录可能包含凭证或账户隐私数据的请求头和请求体。
- 安全敏感问题应尽可能先私下处理。
- 如确认存在漏洞，应发布修复版本，并清楚说明影响范围和升级建议。

## Release Policy / 发布策略

- Use semantic versioning where practical.
- Tag releases in Git and publish matching versions to Packagist.
- Keep `composer.lock` for development reproducibility, while consumers depend on Composer constraints.
- Update the feature coverage matrix when adding or removing public SDK features.

- 尽可能遵循语义化版本。
- 在 Git 中打 tag，并向 Packagist 发布对应版本。
- 保留 `composer.lock` 以便开发环境可复现；使用者仍通过 Composer 约束解析依赖。
- 增删公开 SDK 功能时，同步更新功能覆盖表。

## Official Adoption Goal / 官方接纳目标

The long-term goal is to make this SDK suitable for official adoption by Longbridge. To support that goal, the project should prioritize:

- Clear licensing.
- Stable public APIs.
- Automated tests and CI.
- Accurate API coverage documentation.
- Real integration validation for read-only and WebSocket flows.
- Conservative handling of account-mutating operations.

本项目的长期目标是让该 SDK 达到被长桥官方接纳的质量。为支持这个目标，项目应优先保证：

- 清晰的开源许可。
- 稳定的公开 API。
- 自动化测试和 CI。
- 准确的 API 覆盖文档。
- 对只读接口和 WebSocket 流程进行真实集成验证。
- 谨慎处理会修改账户状态的操作。

## Support Level / 支持级别

This project is provided on a best-effort community maintenance basis unless and until it is transferred to, sponsored by, or officially maintained by Longbridge.

在项目被长桥官方接收、赞助或正式维护之前，本项目按照社区项目的 best-effort 模式维护。
