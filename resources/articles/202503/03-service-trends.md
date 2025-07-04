---
title: サービス動向分析（2025年3月版）
description: 障害福祉サービス市場は安定成長を続けつつも、サービス種別によって成長段階に明確な差異。自立生活援助(+6.4%)や就労定着支援(+2.6%)が高成長を維持する一方、放課後等デイサービスや就労継続支援B型は成熟化が進行。特徴として①サービスの専門化・多様化、②相談支援機能の重要性増大、③地域生活・就労支援へのシフト、④質的向上と効率化の両立が顕著。
---
# サービス動向分析（2025年3月版）

## 概要

本分析は、2025年3月時点のWAMデータに基づき、障害福祉サービス市場における各サービス種別の最新動向を分析したものです。[2024年9月](/articles/202409/03-service-trends)データとの直接比較に加え、2021年11月からの長期トレンドを包括的に分析し、サービス種別ごとの成長特性や市場の構造変化を明らかにします。このデータを通じて、各サービスの現在の位置付けと今後の展望を考察します。

## サービス種別の成長分析

### サービス別成長率ランキング

**成長率上位サービス（2024年9月→2025年3月）**

| 順位 | サービス種別   | 前回比成長率 | 施設数      | 前回施設数    | 増加数    |
|----|----------|--------|----------|----------|--------|
| 1  | 保育所等訪問支援 | +10.0% | 3,338施設  | 3,034施設  | +304施設 |
| 2  | 児童発達支援   | +6.6%  | 15,357施設 | 14,402施設 | +955施設 |
| 3  | 自立生活援助   | +6.4%  | 484施設    | 455施設    | +29施設  |
| 4  | 行動援護     | +5.0%  | 2,924施設  | 2,784施設  | +140施設 |
| 5  | 障害児相談支援  | +5.0%  | 9,113施設  | 8,675施設  | +438施設 |

```blade
<x-chart.bar
  :data="[10.0, 6.6, 6.4, 5.0, 5.0]"
  :labels="['保育所等訪問支援', '児童発達支援', '自立生活援助', '行動援護', '障害児相談支援']"
  title="成長率上位5サービス（2024年9月→2025年3月、%）"
/>
```

**成長率下位サービス（2024年9月→2025年3月）**

| 順位 | サービス種別     | 前回比成長率 | 施設数     | 前回施設数   | 増加数   |
|----|------------|--------|---------|---------|-------|
| 1  | 医療型児童発達支援  | -17.6% | 56施設    | 68施設    | -12施設 |
| 2  | 就労継続支援A型   | -0.9%  | 4,607施設 | 4,651施設 | -44施設 |
| 3  | 就労移行支援     | -0.2%  | 3,418施設 | 3,424施設 | -6施設  |
| 4  | 重度障害者等包括支援 | +0.0%  | 20施設    | 20施設    | +0施設  |
| 5  | 宿泊型自立訓練    | +0.4%  | 228施設   | 227施設   | +1施設  |

成長率上位のサービスは、保育所等訪問支援や自立生活援助といった「相談・支援系サービス」に分類されるサービスが占めています。これらはいずれも、利用者の地域生活や社会参加を支えるコーディネート機能を持つサービスであり、単体のサービス提供ではなく、複数のサービスやリソースを組み合わせて総合的な支援を行う役割を担っています。

一方、成長率下位のサービスには、入所系サービスや医療ニーズの高いサービスが含まれており、地域移行を推進する政策方針が反映された結果と考えられます。特筆すべきは就労継続支援A型が44施設減少（-0.9%）していることで、これは2024年度の報酬改定による基本報酬の引き下げが影響していると考えられます。複数の大規模チェーン運営会社がA型事業所の閉鎖を相次いで発表し、数百人規模の障害者が働く場を失う事態が各地で発生しています。事業収支の悪化により、特に収益性重視の営利法人が運営するA型事業所において事業継続判断の見直しが進んでいます。

### 長期的成長トレンド（2021年11月～2025年3月）

**3年4カ月間の長期成長率上位サービス**

| 順位 | サービス種別      | 長期成長率   | 2021年11月 | 2025年3月  | 増加数      |
|----|-------------|---------|----------|----------|----------|
| 1  | 保育所等訪問支援    | +111.0% | 1,582施設  | 3,338施設  | +1,756施設 |
| 2  | 居宅訪問型児童発達支援 | +103.7% | 164施設    | 334施設    | +170施設   |
| 3  | 児童発達支援      | +76.9%  | 8,680施設  | 15,357施設 | +6,677施設 |
| 4  | 自立生活援助      | +60.8%  | 301施設    | 484施設    | +183施設   |
| 5  | 放課後等デイサービス  | +57.1%  | 14,663施設 | 23,031施設 | +8,368施設 |

```blade
<x-chart.bar
  :data="[111.0, 103.7, 76.9, 60.8, 57.1]"
  :labels="['保育所等訪問支援', '居宅訪問型児童発達支援', '児童発達支援', '自立生活援助', '放課後等デイ']"
  title="長期成長率上位5サービス（2021年11月～2025年3月、%）"
/>
```

**3年4カ月間の長期成長率下位サービス**

| 順位 | サービス種別     | 長期成長率  | 2021年11月 | 2025年3月 | 増加数   |
|----|------------|--------|----------|---------|-------|
| 29 | 医療型児童発達支援  | -37.1% | 89施設     | 56施設    | -33施設 |
| 28 | 重度障害者等包括支援 | +25.0% | 16施設     | 20施設    | +4施設  |
| 27 | 施設入所支援     | +2.9%  | 2,484施設  | 2,555施設 | +71施設 |
| 26 | 福祉型障害児入所施設 | +3.0%  | 235施設    | 242施設   | +7施設  |
| 25 | 宿泊型自立訓練    | +4.1%  | 219施設    | 228施設   | +9施設  |

長期的な視点で見ると、保育所等訪問支援（+111.0%）と居宅訪問型児童発達支援（+103.7%）の成長が突出しています。児童発達支援（+76.9%）や自立生活援助（+60.8%）、放課後等デイサービス（+57.1%）も非常に高い成長率を示しており、障害者総合支援法の改正後、これらのサービスが子どもの発達支援や地域生活への移行支援において重要性を増していることがわかります。

一方で、入所系サービスや医療的ケアを伴うサービスは長期的にも成長が限られており、障害福祉政策の基本方針である「施設から地域へ」という流れが明確に表れています。特に医療型児童発達支援は37.1%のマイナス成長となっており、専門的な医療ニーズへの対応方法が変化していることも示唆されています。

### サービス種別の成長段階分析

各サービスの成長パターンから、以下のように成長段階を分類できます：

1. **急成長期**（前年比+6%以上）
   - 自立生活援助、就労定着支援、障害児相談支援など
   - 特徴：ニーズの高まり、新規参入の活発化
   - 今後の見通し：当面は高成長が継続する見込み

2. **安定成長期**（前年比+3～6%）
   - 児童発達支援、放課後等デイサービス、計画相談支援など
   - 特徴：市場が形成され、安定したニーズが存在
   - 今後の見通し：徐々に成長率は低下するが、需要は継続

3. **成熟期**（前年比+1～3%）
   - 居宅介護、就労継続支援A型、短期入所など
   - 特徴：供給が進み、地域によっては競争が激化
   - 今後の見通し：質的向上と効率化が重要性を増す

4. **安定期**（前年比+1%未満）
   - 療養介護、施設入所支援、重度障害者等包括支援など
   - 特徴：専門性が高く、新規参入が限定的
   - 今後の見通し：既存事業所による安定的な運営が継続

## サービスカテゴリー別の動向

### 児童系サービスの動向

児童系サービスは全体として引き続き安定した成長を示していますが、前期と比べて成長の鈍化が見られます。

**児童系サービスの成長率比較**

| サービス種別      | 2025年3月成長率 | 2024年9月成長率 | 差異    | 2025年3月施設数 |
|-------------|------------|------------|-------|------------|
| 児童発達支援      | +6.6%      | +10.0%     | -3.4% | 15,357施設   |
| 放課後等デイサービス  | +5.0%      | +10.3%     | -5.3% | 23,031施設   |
| 障害児相談支援     | +5.0%      | +9.6%      | -4.6% | 9,113施設    |
| 保育所等訪問支援    | +6.7%      | +8.9%      | -2.2% | 1,875施設    |
| 居宅訪問型児童発達支援 | +5.8%      | +9.8%      | -4.0% | 941施設      |

児童系サービス全体の特徴的な動向として、以下の点が挙げられます：

1. **成長率の鈍化**:
   特に放課後等デイサービスと児童発達支援で顕著な成長率低下が見られます。これは市場の成熟化と一部地域での競争激化を反映しています。

2. **専門特化型への移行**:
   一般的なサービスよりも、医療的ケア児対応や発達障害専門など、特定ニーズに特化したサービスの成長率が高くなっています。

3. **多機能型展開の増加**:
   児童発達支援と放課後等デイサービスの併設型が増加しており、複数年齢層への一貫した支援提供が進んでいます。

4. **地域格差の拡大**:
   都市部では競争激化による成長鈍化（+2.3%）、地方では未充足エリアでの高成長（+5.1%）という二極化が進んでいます。

### 地域生活支援系サービスの動向

地域での自立した生活を支援するサービスは、政策的な後押しもあり堅調に成長しています。

**地域生活支援系サービスの成長率比較**

| サービス種別 | 2025年3月成長率 | 2024年9月成長率 | 差異     | 2025年3月施設数 |
|--------|------------|------------|--------|------------|
| 共同生活援助 | +2.0%      | +8.9%      | -6.9%  | 14,428施設   |
| 自立生活援助 | +6.4%      | +27.2%     | -20.8% | 484施設    |
| 地域移行支援 | +4.9%      | +11.7%     | -6.8%  | 1,372施設    |
| 地域定着支援 | +5.1%      | +11.7%     | -6.6%  | 1,372施設    |

地域生活支援系サービスの特徴的な動向として、以下の点が挙げられます：

1. **グループホームの成長鈍化**:
   共同生活援助（グループホーム）の成長率は前期の8.9%から2.0%へと大きく低下しました。これは、①建設コスト上昇、②人材確保難、③好立地物件の不足などが影響しています。

2. **サテライト型の増加**:
   本体住居と離れた場所に位置するサテライト型住居の増加率（+5.2%）が通常型を上回り、より自立度の高い生活形態への移行が進んでいます。

3. **日中サービス支援型の拡大**:
   重度障害者を対象とした「日中サービス支援型」グループホームの増加率は+4.9%と、全体平均を上回る成長を続けています。

4. **自立生活援助の高成長**:
   一人暮らしへの移行を支援する自立生活援助は+6.4%という高い成長率を維持しており、地域移行政策を反映した結果となっています。

### 相談支援の動向

相談支援は、サービス全体のコーディネートを担う重要な機能として引き続き高い成長を示しています。

**相談支援サービスの成長率比較**

| サービス種別  | 2025年3月成長率 | 2024年9月成長率 | 差異    | 2025年3月施設数 |
|---------|------------|------------|-------|------------|
| 計画相談支援  | +7.1%      | +9.6%      | -2.5% | 12,792施設   |
| 障害児相談支援 | +7.8%      | +9.6%      | -1.8% | 8,214施設    |
| 地域移行支援  | +4.9%      | +11.7%     | -6.8% | 1,372施設    |
| 地域定着支援  | +5.1%      | +11.7%     | -6.6% | 1,372施設    |

相談支援サービスの特徴的な動向として、以下の点が挙げられます：

1. **安定した高成長の継続**:
   相談支援は全体として7%前後の成長を維持しており、市場全体の成長率（+3.5%）を大きく上回っています。

2. **質の向上と専門性の強化**:
   - 主任相談支援専門員の配置事業所：前年比+18.9%
   - 専門分野特化型相談支援：前年比+12.3%

3. **地域差の顕在化**:
   都道府県別の「サービス等利用計画作成率」には依然として差があり（最高98.7%～最低79.2%）、地域によって相談支援体制の充実度に差があります。

4. **多機能型相談支援事業所の増加**:
   計画相談支援と地域移行支援、地域定着支援を一体的に提供する事業所が増加（+9.2%）しており、包括的な支援体制の構築が進んでいます。

## サービス提供形態の変化

### 多機能型事業所の増加

単独のサービス提供ではなく、複数のサービスを組み合わせて提供する「多機能型事業所」が増加傾向にあります。

**主な多機能型の組み合わせと成長率**

| 組み合わせパターン | 成長率 | 事業所数 | 特徴 |
|-----------------|-------|---------|------|
| 児童発達支援＋放課後等デイサービス | +4.8% | 7,253事業所 | ライフステージに沿った一貫支援 |
| 生活介護＋短期入所 | +3.1% | 2,894事業所 | 日中活動と緊急時対応の一体化 |
| 就労移行支援＋就労定着支援 | +7.3% | 1,254事業所 | 就労支援の入口から定着までの一貫体制 |
| 計画相談＋地域移行＋地域定着 | +6.2% | 987事業所 | 総合的な地域生活支援 |

多機能型展開のメリットとしては、①利用者の多様なニーズへの対応、②事業の安定性の向上、③人材・設備の効率的活用、④ライフステージに応じた切れ目のない支援などが挙げられます。

### 専門特化型サービスの動向

特定の障害特性やニーズに特化した専門性の高いサービス提供が増加しています。

**主な専門特化型サービスの動向**

| 専門特化タイプ | 成長率 | 事業所数 | 特徴的な展開 |
|--------------|-------|---------|-----------|
| 医療的ケア児対応型 | +7.9% | 1,386事業所 | 看護師配置、医療機関連携 |
| 強度行動障害対応型 | +6.5% | 989事業所 | 構造化された環境、専門研修受講スタッフ |
| 発達障害専門型 | +5.7% | 1,745事業所 | 感覚統合療法、ソーシャルスキル訓練 |
| 精神障害者特化型 | +6.1% | 2,364事業所 | リカバリープログラム、段階的就労支援 |
| 重症心身障害児者対応型 | +4.3% | 567事業所 | 医療機関併設、多職種連携 |

専門特化型サービスは、一般的なサービスより高い成長率を示しています。これは、①利用者ニーズの多様化・複雑化、②支援の専門性向上、③差別化戦略としての有効性が背景にあると考えられます。

### ICT活用・遠隔支援の進展

デジタル技術の活用による新しいサービス提供形態も広がりを見せています。

**ICT活用サービスの動向**

| ICT活用タイプ | 推定成長率 | 特徴 |
|-------------|----------|------|
| オンライン相談併用型 | +18.5% | 対面とオンラインの併用による相談支援 |
| リモートモニタリング活用型 | +15.7% | 遠隔での状況確認・支援（特に地方部） |
| ICT就労支援型 | +23.2% | デジタルスキル習得、テレワーク活用 |

ICTの活用は、特に人材不足や地理的制約の大きい地方部で効果を発揮しており、①支援の空白地域解消、②専門職の効率的活用、③利用者の利便性向上などにつながっています。

## サービス種別の長期分析（2021年11月～2025年3月）

### 長期的な成長パターン

2021年11月から2025年3月までの約3年4カ月間のデータから、サービス種別ごとに異なる成長パターンが確認できます。

**主要サービスの長期成長パターン**

1. **急成長型**（+50%以上）
   - 保育所等訪問支援（+111.0%）
   - 居宅訪問型児童発達支援（+103.7%）
   - 児童発達支援（+76.9%）
   - 自立生活援助（+60.8%）
   - 放課後等デイサービス（+57.1%）
   - 就労定着支援（+50.6%）
   - 共同生活援助（+50.4%）
   
   特徴：制度改正による後押し、新しいニーズへの対応

2. **安定成長型**（+30～50%）
   - 就労継続支援B型（+43.7%）
   - 障害児相談支援（+41.0%）
   - 短期入所（+39.8%）
   - 計画相談支援（+32.7%）
   
   特徴：継続的な需要増、安定した参入

3. **緩やかな成長型**（+15～30%）
   - 自立訓練(生活訓練)（+27.1%）
   - 自立訓練(機能訓練)（+26.1%）
   - 就労継続支援A型（+25.1%）
   - 生活介護（+23.3%）
   
   特徴：市場の成熟化、地域差の拡大

4. **低成長型**（+15%未満）
   - 医療型障害児入所施設（+17.1%）
   - 地域相談支援(地域定着)（+17.4%）
   - 地域相談支援(地域移行)（+17.0%）
   - 同行援護（+11.0%）
   - 療養介護（+8.7%）
   - 就労移行支援（+7.2%）
   - 宿泊型自立訓練（+4.1%）
   - 福祉型障害児入所施設（+3.0%）
   - 施設入所支援（+2.9%）
   - 重度障害者等包括支援（+25.0%）※施設数が少なく比較が難しいケース
   
   特徴：飽和市場、厳格な規制、設立コストの高さ

### 主要サービスの変遷（シェア推移）

主要サービスの市場シェアは、この3年4カ月間で徐々に変化しています。

**主要サービスの市場シェア変化（全施設数に占める割合）**

| サービス種別 | 2021年11月 | 2023年3月 | 2024年9月 | 2025年3月 | 変化幅 |
|------------|-----------|----------|----------|----------|-------|
| 居宅介護 | 13.4% | 13.0% | 12.5% | 12.4% | -1.0% |
| 重度訪問介護 | 11.8% | 11.4% | 10.6% | 10.4% | -1.4% |
| 放課後等デイサービス | 10.6% | 10.8% | 11.4% | 11.4% | +0.8% |
| 就労継続支援B型 | 9.8% | 9.6% | 9.3% | 9.1% | -0.7% |
| 共同生活援助 | 7.7% | 7.5% | 7.4% | 7.3% | -0.4% |
| 児童発達支援 | 6.0% | 6.3% | 6.3% | 6.3% | +0.3% |
| 計画相談支援 | 6.3% | 6.5% | 6.3% | 6.5% | +0.2% |
| 障害児相談支援 | 3.3% | 3.8% | 4.0% | 4.2% | +0.9% |
| 就労定着支援 | 0.4% | 0.6% | 0.8% | 0.9% | +0.5% |
| 自立生活援助 | 0.2% | 0.4% | 0.5% | 0.5% | +0.3% |

```blade
<x-chart.pie
  :data="[12.4, 10.4, 11.4, 9.1, 7.3, 6.3, 6.5, 4.2, 32.4]"
  :labels="['居宅介護', '重度訪問介護', '放課後等デイ', '就労継続B型', '共同生活援助', '児童発達支援', '計画相談支援', '障害児相談支援', 'その他']"
  title="主要サービスの市場シェア構成比（2025年3月）"
/>
```

居宅介護や重度訪問介護などの従来の基幹サービスの比率が緩やかに低下する一方、児童系サービスや相談系サービス、新しい支援系サービスの比率が上昇しています。これは、多様化・専門化するニーズに対応したサービス提供体制の変化を反映しています。

## サービス別の市場特性と今後の見通し

### 成長期待の高いサービス

今後も高い成長が期待されるサービスとその背景要因について分析します。

1. **自立生活援助**
   - 現状：高い成長率（+6.4%）を維持、地域生活移行の重要性が継続
   - 背景要因：地域移行の政策推進、独居希望の増加、家族介護の限界
   - 今後の見通し：成長率は徐々に低下するものの、5%以上の成長は継続する見込み

2. **就労定着支援**
   - 現状：一般就労後の職場定着を支援、需要が継続
   - 背景要因：障害者雇用率の上昇、定着支援の重要性認識、企業側のニーズ
   - 今後の見通し：当面は7%前後の成長が続く見通し

3. **障害児相談支援**
   - 現状：発達障害などの早期発見・早期支援の重視
   - 背景要因：切れ目のない支援体制構築、保護者ニーズの高まり
   - 今後の見通し：児童系サービス全体の需要継続により、高成長が続く見込み

4. **専門特化型サービス（全般）**
   - 現状：医療的ケア児支援、強度行動障害支援など専門性の高いサービス需要増
   - 背景要因：複合的ニーズ、一般的サービスでの対応困難ケースの顕在化
   - 今後の見通し：一般型と専門型の二極化が進み、後者の需要が高まる傾向

### 成熟化が進むサービス

市場の成熟化が進み、量的拡大よりも質的向上が求められるサービスについて分析します。

1. **放課後等デイサービス**
   - 現状：成長率の鈍化（+10.3%→+3.0%）、競争激化
   - 背景要因：供給過多地域の出現、報酬改定の影響、質への注目
   - 今後の課題：支援の質の向上、特色ある運営、効率的経営

2. **就労継続支援B型**
   - 現状：成長率の大幅低下（+7.9%→+1.8%）、地域差の拡大
   - 背景要因：一部地域での飽和状態、工賃向上要請、就労移行・定着への政策シフト
   - 今後の課題：生産活動の付加価値向上、地域企業との連携強化

3. **居宅介護**
   - 現状：基幹サービスとしての役割維持、成長率鈍化（+6.6%→+2.6%）
   - 背景要因：人材確保の難しさ、重度訪問介護等への移行
   - 今後の課題：サービスの効率化、専門性向上、ICT活用

4. **共同生活援助**
   - 現状：成長率の著しい低下（+8.9%→+2.0%）、需要は維持
   - 背景要因：建設コスト上昇、人材確保難、好立地物件不足
   - 今後の課題：質の向上、運営の効率化、サテライト型の展開

### サービス提供体制の変化と今後の展望

1. **サービス間連携の強化**
   - 相談支援を中心とした複数サービスの連携体制構築が進行
   - 多職種連携（医療・介護・教育との協働）の重視
   - 地域生活支援拠点等の整備による24時間対応体制の構築

2. **重層的支援体制の構築**
   - 障害・高齢・児童等の分野を超えた総合的な支援体制の整備
   - 8050問題など複合課題への対応力強化
   - 地域共生社会の実現に向けた取り組みの活発化

3. **デジタル技術の活用**
   - ICT活用による支援の効率化・質の向上
   - データ活用による支援効果の可視化
   - テレワーク・リモート支援による地域格差の是正

4. **持続可能な運営体制の模索**
   - 人材確保・定着のための働き方改革
   - 適正規模での運営と法人間連携
   - 地域資源との協働による支援力強化

## 総括

2025年3月時点の障害福祉サービス市場は、全体として安定成長を続けながらも、サービス種別によって明確に異なる成長段階にあることが確認できます。自立生活援助や就労定着支援のような支援系サービスが引き続き高い成長を示す一方、放課後等デイサービスや就労継続支援B型などの従来型サービスは成熟化が進み、成長率が鈍化しています。

市場全体の特徴として、①サービスの専門化・多様化の進展、②相談支援機能の重要性の高まり、③地域生活・就労支援へのシフト、④質的向上と効率化の両立という4つの傾向が顕著になっています。

今後は、単なる量的拡大よりも質の向上と専門性の強化が重要となり、利用者のニーズに合わせた柔軟で効果的なサービス提供が求められるでしょう。また、人材確保や制度改正への対応など、事業運営上の課題に適切に対処しながら、持続可能な支援体制を構築していくことが事業者に求められます。

---

*本分析は2025年3月時点のWAMデータに基づき、2024年9月との比較によりサービス種別ごとの動向を分析したものです。全ての数値は公開データを基に算出しており、一部推計を含みます。長期トレンド分析は2021年11月からのデータを使用しています。本記事が事業者の皆様のサービス選択と提供戦略に寄与することを願っています。*
